<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeePayscaleDetail;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class AppointmentLetterPlaceholderService
{
    public static function render(string $html, ?Employee $employee, ?EmployeePayscaleDetail $payscaleDetail): string
    {
        return self::formatAppointmentClauses(
            strtr($html, self::replacements($employee, $payscaleDetail))
        );
    }

    public static function variables(): array
    {
        return [
            'Employee Variables' => [
                'Employee Name' => '[[EMPLOYEE_NAME]]',
                'Designation' => '[[DESIGNATION]]',
                'Effect From' => '[[EFFECT_FROM]]',
                'Basic Salary' => '[[BASIC_SALARY]]',
                'Probation Period' => '[[PROBATION_PERIOD]]',
                'Working Days in Month' => '[[WORKING_DAYS_IN_MONTH]]',
                'Contract Start Date' => '[[CONTRACT_START_DATE]]',
                'Contract End Date' => '[[CONTRACT_END_DATE]]',
                'Contract Duration' => '[[CONTRACT_DURATION]]',
                'Application Date' => '[[APPLICATION_DATE]]',
                'Interview Date' => '[[INTERVIEW_DATE]]',
                'Per Day Salary' => '[[PER_DAY_SALARY]]',
            ],
        ];
    }

    public static function formatAppointmentClauses(string $html): string
    {
        if (trim($html) === '' || !self::containsClausePlaceholder($html)) {
            return $html;
        }

        $html = self::normalizeClauseParagraphBreaks($html);
        $html = self::splitParagraphsByBreaks($html);
  
        $previousState = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');
            $wrappedHtml = '<div id="appointment-clause-root">' . $html . '</div>';

            $document->loadHTML(
                mb_convert_encoding($wrappedHtml, 'HTML-ENTITIES', 'UTF-8'),
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $root = $document->getElementById('appointment-clause-root');

            if (!$root instanceof DOMElement) {
                return $html;
            }

            self::transformClauseNodes($root);
            self::alignTopLevelContent($root);

            $output = '';
            foreach ($root->childNodes as $childNode) {
                $output .= $document->saveHTML($childNode);
            }

            return self::finalizeRenderedParagraphs($output);
        } catch (\Throwable $exception) {
            return $html;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);
        }
    }

    private static function replacements(?Employee $employee, ?EmployeePayscaleDetail $payscaleDetail): array
    {
        $payscaleDetail?->loadMissing('scale.employeeScaleHeads.salaryHeads');
        $employee?->loadMissing(['designation', 'currentContract']);

        $contract = $employee?->currentContract;
        
        $durationString = '';
        if ($contract) {
            $from = Carbon::parse($contract->from_date);
            $to = Carbon::parse($contract->to_date);
            $diff = $from->diff($to);
            $parts = [];
            if ($diff->y > 0) {
                $parts[] = $diff->y . ' ' . ($diff->y == 1 ? 'year' : 'years');
            }
            if ($diff->m > 0) {
                $parts[] = $diff->m . ' ' . ($diff->m == 1 ? 'month' : 'months');
            }
            if ($diff->d > 0) {
                $parts[] = $diff->d . ' ' . ($diff->d == 1 ? 'day' : 'days');
            }
            $durationString = implode(', ', $parts);
            if (empty($durationString)) {
                $durationString = '0 days';
            }
        }

        $gross = $payscaleDetail ? $payscaleDetail->resolved_gross_salary : 0;
        $workingDays = $payscaleDetail ? (int) $payscaleDetail->working_days : 0;
        $perDaySalary = $workingDays > 0 ? ($gross / $workingDays) : 0;

        return [
            '[[EMPLOYEE_NAME]]' => self::boldUnderline($employee->name ?? ''),
            '[[DESIGNATION]]' => self::boldUnderline(optional($employee?->designation)->name),
            '[[EFFECT_FROM]]' => self::underline(self::formatDate($payscaleDetail->effect_from ?? $employee->company_doj ?? null)),
            '[[BASIC_SALARY]]' => self::boldUnderline(number_format((float) ($payscaleDetail->resolved_basic_salary ?? 0), 2)),
            '[[PROBATION_PERIOD]]' => self::underline((string) ($employee->probation_period ?? '')),
            '[[WORKING_DAYS_IN_MONTH]]' => self::underline((string) ($payscaleDetail->working_days ?? '')),
            '[[CONTRACT_START_DATE]]' => self::underline(self::formatDate($contract->from_date ?? null)),
            '[[CONTRACT_END_DATE]]' => self::underline(self::formatDate($contract->to_date ?? null)),
            '[[CONTRACT_DURATION]]' => self::underline($durationString),
            '[[APPLICATION_DATE]]' => self::underline(self::formatDate($employee->application_date ?? null)),
            '[[INTERVIEW_DATE]]' => self::underline(self::formatDate($employee->interview_date ?? null)),
            '[[PER_DAY_SALARY]]' => self::boldUnderline((string) round($perDaySalary)),
        ];
    }

    private static function boldUnderline($value): string
    {
        $value = trim((string) $value);

        return '<span class="appointment-inline-field appointment-inline-field-bold">' . e($value) . '</span>';
    }

    private static function underline($value): string
    {
        $value = trim((string) $value);

        return '<span class="appointment-inline-field">' . e($value) . '</span>';
    }

    private static function formatDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('d M Y');
        } catch (\Throwable $exception) {
            return '';
        }
    }

    private static function transformClauseNodes(DOMElement $parent): void
    {
        $children = [];
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $children[] = $child;
            }
        }

        foreach ($children as $child) {
            self::transformClauseNodes($child);

            self::convertElementToClause($child);
        }
    }

    private static function convertElementToClause(DOMElement $element): bool
    {
        $match = self::extractLeadingClauseMatch($element);

        if ($match === null) {
            return false;
        }

        /** @var DOMText $textNode */
        $textNode = $match['textNode'];
        $textNode->nodeValue = self::removeLeadingClausePlaceholder($textNode->nodeValue);

        self::cleanupLeadingWhitespace($element);
        self::normalizeLeadingClauseWhitespace($element);

        $document = $element->ownerDocument;
        if (!$document instanceof DOMDocument || !$element->parentNode) {
            return false;
        }

        $wrapper = $document->createElement('table');
        $wrapper->setAttribute('class', 'appointment-clause');
        $wrapper->setAttribute('width', '100%');
        $wrapper->setAttribute('cellpadding', '0');
        $wrapper->setAttribute('cellspacing', '0');
        $wrapper->setAttribute('border', '0');
        $wrapper->setAttribute('style', 'width:100%;border-collapse:collapse;margin:0 0 8px 0;');

        $row = $document->createElement('tr');

        $number = $document->createElement('td');
        $number->setAttribute('class', 'appointment-clause-number');
        $number->setAttribute('width', '45');
        $number->setAttribute('valign', 'top');
        $number->setAttribute(
            'style',
            'width:45px;min-width:45px;max-width:45px;vertical-align:top;text-align:left;padding:0 5px 0 0;margin:0;white-space:nowrap;'
        );
        $number->appendChild($document->createTextNode($match['label']));

        $text = $document->createElement('td');
        $text->setAttribute('class', trim('appointment-clause-text ' . $element->getAttribute('class')));
        $text->setAttribute('valign', 'top');
        $text->setAttribute('style', 'vertical-align:top;text-align:left;padding:0;margin:0;line-height:1.8;');

        foreach (['style', 'align', 'dir'] as $attribute) {
            if ($element->hasAttribute($attribute)) {
                $text->setAttribute($attribute, $element->getAttribute($attribute));
            }
        }

        while ($element->firstChild) {
            $text->appendChild($element->firstChild);
        }

        self::normalizeClauseTextContainer($text);

        $row->appendChild($number);
        $row->appendChild($text);
        $wrapper->appendChild($row);

        $element->parentNode->replaceChild($wrapper, $element);

        return true;
    }

    private static function extractLeadingClauseMatch(DOMElement $element): ?array
    {
        $textNode = self::findFirstMeaningfulTextNode($element);

        if (!$textNode instanceof DOMText) {
            return null;
        }

        $placeholder = self::parseLeadingClausePlaceholder($textNode->nodeValue);

        if ($placeholder === null) {
            return null;
        }

        if (!self::hasOnlyIgnorableContentBefore($element, $textNode)) {
            return null;
        }

        return [
            'textNode' => $textNode,
            'label' => self::buildClauseLabel($placeholder['main'], $placeholder['sub'] ?? null),
        ];
    }

    private static function buildClauseLabel(string $main, ?string $sub): string
    {
        $sub = $sub !== null ? trim($sub) : null;

        if ($sub === null || $sub === '') {
            return $main . '.';
        }

        return $main . '(' . $sub . ').';
    }

    private static function findFirstMeaningfulTextNode(DOMNode $node): ?DOMText
    {
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMText && trim($child->nodeValue) !== '') {
                return $child;
            }

            if ($child instanceof DOMElement) {
                $result = self::findFirstMeaningfulTextNode($child);

                if ($result instanceof DOMText) {
                    return $result;
                }
            }
        }

        return null;
    }

    private static function hasOnlyIgnorableContentBefore(DOMElement $container, DOMText $target): bool
    {
        foreach ($container->childNodes as $child) {
            if ($child === $target) {
                return self::parseLeadingClausePlaceholder($target->nodeValue ?? '') !== null;
            }

            if ($child instanceof DOMText && trim($child->nodeValue) !== '') {
                return false;
            }

            if ($child instanceof DOMElement) {
                if (strtolower($child->nodeName) === 'br') {
                    continue;
                }

                if (self::containsNode($child, $target)) {
                    return self::hasOnlyIgnorableContentBefore($child, $target);
                }

                if (trim($child->textContent) !== '') {
                    return false;
                }
            }
        }

        return false;
    }

    private static function containsNode(DOMNode $parent, DOMNode $target): bool
    {
        foreach ($parent->childNodes as $child) {
            if ($child === $target) {
                return true;
            }

            if ($child->hasChildNodes() && self::containsNode($child, $target)) {
                return true;
            }
        }

        return false;
    }

    private static function cleanupLeadingWhitespace(DOMElement $element): void
    {
        while ($element->firstChild instanceof DOMText && trim($element->firstChild->nodeValue) === '') {
            $element->removeChild($element->firstChild);
        }
    }

    private static function containsClausePlaceholder(string $html): bool
    {
        return preg_match('/\[\[(CLAUSE:\d+(?::[a-zA-Z0-9]+)?|NO:\d+|ALP:[a-zA-Z]|ALPHA:[a-zA-Z])\]\]/i', $html) === 1;
    }

    private static function parseLeadingClausePlaceholder(string $value): ?array
    {
        if (preg_match('/^[\s\x{00A0}]*\[\[CLAUSE:(\d+)(?::([a-zA-Z0-9]+))?\]\]/iu', $value, $matches)) {
            return [
                'main' => $matches[1],
                'sub' => isset($matches[2]) ? strtolower(trim($matches[2])) : null,
            ];
        }

        if (preg_match('/^[\s\x{00A0}]*\[\[NO:(\d+)\]\][\s\x{00A0}]*(?:\[\[(?:ALP|ALPHA):([a-zA-Z])\]\])?/iu', $value, $matches)) {
            return [
                'main' => $matches[1],
                'sub' => isset($matches[2]) ? strtolower(trim($matches[2])) : null,
            ];
        }

        return null;
    }

    private static function removeLeadingClausePlaceholder(string $value): string
    {
        return preg_replace(
            '/^[\s\x{00A0}]*(?:\[\[CLAUSE:\d+(?::[a-zA-Z0-9]+)?\]\]|\[\[NO:\d+\]\][\s\x{00A0}]*(?:\[\[(?:ALP|ALPHA):[a-zA-Z]\]\])?)[\s\x{00A0}]*/iu',
            '',
            $value,
            1
        ) ?? $value;
    }

    private static function normalizeLeadingClauseWhitespace(DOMElement $element): void
    {
        $textNode = self::findFirstMeaningfulTextNode($element);

        if (!$textNode instanceof DOMText) {
            return;
        }

        $textNode->nodeValue = preg_replace('/^[\s\x{00A0}]+/u', '', $textNode->nodeValue) ?? $textNode->nodeValue;
    }

    private static function normalizeClauseTextContainer(DOMElement $container): void
    {
        self::removeLeadingIgnorableNodes($container);

        $textNode = self::findFirstMeaningfulTextNode($container);

        if ($textNode instanceof DOMText) {
            $textNode->nodeValue = preg_replace('/^[\s\x{00A0}]+/u', '', $textNode->nodeValue) ?? $textNode->nodeValue;
        }
    }

    private static function removeLeadingIgnorableNodes(DOMElement $container): void
    {
        while ($container->firstChild) {
            $firstChild = $container->firstChild;

            if ($firstChild instanceof DOMText) {
                if (trim(str_replace("\xC2\xA0", ' ', $firstChild->nodeValue)) === '') {
                    $container->removeChild($firstChild);
                    continue;
                }

                $firstChild->nodeValue = preg_replace('/^[\s\x{00A0}]+/u', '', $firstChild->nodeValue) ?? $firstChild->nodeValue;
                break;
            }

            if ($firstChild instanceof DOMElement) {
                if (self::elementHasMeaningfulText($firstChild)) {
                    self::removeLeadingIgnorableNodes($firstChild);

                    if (trim($firstChild->textContent) === '' && !$firstChild->hasChildNodes()) {
                        $container->removeChild($firstChild);
                        continue;
                    }

                    break;
                }

                $container->removeChild($firstChild);
                continue;
            }

            break;
        }
    }

    private static function elementHasMeaningfulText(DOMElement $element): bool
    {
        if (trim(str_replace("\xC2\xA0", ' ', $element->textContent)) !== '') {
            return true;
        }

        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && self::elementHasMeaningfulText($child)) {
                return true;
            }
        }

        return false;
    }
    private static function splitParagraphsByBreaks(string $html): string
    {
        return preg_replace_callback(
            '/<p([^>]*)>(.*?)<\/p>/is',
            function (array $matches): string {
                $attributes = $matches[1] ?? '';
                $innerHtml = $matches[2] ?? '';

                if (!preg_match('/<br\s*\/?>/i', $innerHtml)) {
                    return $matches[0];
                }

                $parts = preg_split('/<br\s*\/?>/i', $innerHtml) ?: [];
                $paragraphs = [];

                foreach ($parts as $part) {
                    $normalized = str_replace('&nbsp;', ' ', $part);

                    if (trim(strip_tags($normalized)) === '') {
                        continue;
                    }

                    $paragraphs[] = '<p' . $attributes . '>' . trim($part) . '</p>';
                }

                return !empty($paragraphs) ? implode('', $paragraphs) : $matches[0];
            },
            $html
        ) ?? $html;
    }

    private static function normalizeClauseParagraphBreaks(string $html): string
    {
        return preg_replace(
            '/(?:<br\s*\/?>\s*)+(?=\[\[(?:NO|CLAUSE):\d+\]\])/i',
            '</p><p>',
            $html
        ) ?? $html;
    }

    private static function alignTopLevelContent(DOMElement $root): void
    {
        $children = [];
        foreach ($root->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->parentNode !== $root) {
                continue;
            }

            if ($child instanceof DOMText) {
                $text = trim(str_replace("\xC2\xA0", ' ', $child->nodeValue));

                if ($text === '') {
                    continue;
                }

                $root->replaceChild(self::buildIndentedTableNode($root->ownerDocument, $text), $child);
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            if (strtolower($child->tagName) === 'table' && str_contains(' ' . $child->getAttribute('class') . ' ', ' appointment-clause ')) {
                continue;
            }

            if (!in_array(strtolower($child->tagName), ['p', 'div', 'ul', 'ol'], true)) {
                continue;
            }

            if (trim(str_replace("\xC2\xA0", ' ', $child->textContent)) === '') {
                continue;
            }

            $root->replaceChild(self::buildIndentedTableNode($root->ownerDocument, $child), $child);
        }
    }

    private static function buildIndentedTableNode(?DOMDocument $document, DOMNode|string $content): DOMElement
    {
        $wrapper = $document->createElement('table');
        $wrapper->setAttribute('class', 'appointment-indent');
        $wrapper->setAttribute('width', '100%');
        $wrapper->setAttribute('cellpadding', '0');
        $wrapper->setAttribute('cellspacing', '0');
        $wrapper->setAttribute('border', '0');
        $wrapper->setAttribute('style', 'width:100%;border-collapse:collapse;margin:0 0 8px 0;');

        $row = $document->createElement('tr');

        $spacer = $document->createElement('td');
        $spacer->setAttribute('class', 'appointment-clause-number');
        $spacer->setAttribute('width', '45');
        $spacer->setAttribute('valign', 'top');
        $spacer->setAttribute('style', 'width:45px;min-width:45px;max-width:45px;vertical-align:top;text-align:left;padding:0 5px 0 0;margin:0;white-space:nowrap;');
        $spacer->appendChild($document->createTextNode(''));

        $text = $document->createElement('td');
        $text->setAttribute('class', 'appointment-clause-text');
        $text->setAttribute('valign', 'top');
        $text->setAttribute('style', 'vertical-align:top;text-align:left;padding:0;margin:0;line-height:1.8;');

        if (is_string($content)) {
            $text->appendChild($document->createTextNode($content));
        } elseif ($content instanceof DOMElement && in_array(strtolower($content->tagName), ['p', 'div'], true)) {
            foreach ($content->childNodes as $childNode) {
                $text->appendChild($childNode->cloneNode(true));
            }
        } else {
            $text->appendChild($content->cloneNode(true));
        }

        $row->appendChild($spacer);
        $row->appendChild($text);
        $wrapper->appendChild($row);

        return $wrapper;
    }

    private static function finalizeRenderedParagraphs(string $html): string
    {
        $html = preg_replace_callback(
            '/<p([^>]*)>\s*\[\[(?:NO|CLAUSE):(\d+)\]\]\s*(?:\[\[(?:ALP|ALPHA):([a-zA-Z])\]\])?\s*(.*?)<\/p>/is',
            function (array $matches): string {
                $main = $matches[2];
                $sub = isset($matches[3]) && $matches[3] !== '' ? strtolower($matches[3]) : null;
                $text = trim($matches[4]);
                $label = $sub ? $main . '(' . $sub . ').' : $main . '.';

                return self::buildClauseTableHtml($label, $text);
            },
            $html
        ) ?? $html;

        return preg_replace_callback(
            '/<p([^>]*)>(.*?)<\/p>/is',
            function (array $matches): string {
                $text = trim($matches[2]);

                if ($text === '') {
                    return '';
                }

                return self::buildIndentedTableHtmlString($text);
            },
            $html
        ) ?? $html;
    }

    private static function buildClauseTableHtml(string $label, string $text): string
    {
        return '<table class="appointment-clause" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;margin:0 0 8px 0;">'
            . '<tr>'
            . '<td class="appointment-clause-number" width="45" valign="top" style="width:45px;min-width:45px;max-width:45px;vertical-align:top;text-align:left;padding:0 5px 0 0;margin:0;white-space:nowrap;">'
            . e($label)
            . '</td>'
            . '<td class="appointment-clause-text" valign="top" style="vertical-align:top;text-align:left;padding:0;margin:0;line-height:1.8;">'
            . $text
            . '</td>'
            . '</tr>'
            . '</table>';
    }

    private static function buildIndentedTableHtmlString(string $text): string
    {
        return '<table class="appointment-indent" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;margin:0 0 8px 0;">'
            . '<tr>'
            . '<td class="appointment-clause-number" width="45" valign="top" style="width:45px;min-width:45px;max-width:45px;vertical-align:top;text-align:left;padding:0 5px 0 0;margin:0;white-space:nowrap;"></td>'
            . '<td class="appointment-clause-text" valign="top" style="vertical-align:top;text-align:left;padding:0;margin:0;line-height:1.8;">'
            . $text
            . '</td>'
            . '</tr>'
            . '</table>';
    }
}
