{{ Form::open(array('url' => 'appointment-letter-store')) }}
<div class="modal-body">
    <style>
    .ck-editor__editable_inline {
        min-height: 400px;
        border: 1px solid gray !important;
    }
    </style>
    <div class="row">
        <div class="form-group col-md-4">
            {!! Form::label('no', __('No'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
            {!! Form::number('no', old('no'), ['class' => 'form-control','required' => 'required']) !!}
        </div>
        {{-- //type of letter  --}}
        <div class="form-group col-md-4">
            {!! Form::label('type', __('Type'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
            {!! Form::select('type', ['regular' => 'Regular', 'adhoc' => 'Adhoc', 'visiting' => 'Visiting'], old('type'), ['class' => 'form-control select','required' => 'required']) !!}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('date', __('Date'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
            {!! Form::date('date',old('date'), ['class' => 'form-control']) !!}
        </div>
    </div>
    <div id="editor-container">
        {{-- <div id="editor" class="editor"></div> --}}
        <textarea name="datacontent" class="editor">{{ old('datacontent') }}</textarea>
    </div>
    <small class="text-muted d-block mt-2">
        {{ __('Use clause placeholders like [[CLAUSE:1]], [[CLAUSE:6:a]], or [[NO:6]][[ALP:a]] at the start of a paragraph for aligned numbering in preview, print, and PDF.') }}
    </small>
    @include('employee.appointmentletter.variables', ['variables' => $variables ?? []])

    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-outline-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-outline-primary">
    </div>
</div>
{{ Form::close() }}


    <script src="{{ asset('js/ckeditor.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/45.2.1/translations/en.umd.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5-premium-features/45.2.1/translations/en.umd.js"></script>
<script>
 ClassicEditor
			.create( document.querySelector( '.editor' ), {
				language: 'en',
				toolbar: {
					items: [
						'|',
						'bold',
						'underline',
						'italic',
						'link',
						'bulletedList',
						'numberedList',
						'|',
						'alignment',
						'indent',
						'outdent',
						'|',
						'fontColor',
						'fontBackgroundColor',
						'fontSize',
						'fontFamily',
						'highlight',
						'|',
						'insertTable',
						'imageInsert',
						'mediaEmbed',
						'blockQuote',
						'codeBlock',
						'specialCharacters',
						'removeFormat',
						'htmlEmbed',
						'pageBreak',
						'|',
						'exportWord',
						'imageUpload',
						'heading'
					]
				},
				language: 'fr',
				image: {
					toolbar: [
						'imageTextAlternative',
						'imageStyle:full',
						'imageStyle:side',
						'linkImage'
					]
				},
				table: {
					contentToolbar: [
						'tableColumn',
						'tableRow',
						'mergeTableCells',
						'tableCellProperties',
						'tableProperties'
					]
				},
				licenseKey: '',
				
			} )
			.then( editor => {
				window.editor = editor;
                window.appointmentLetterEditor = editor;

                editor.keystrokes.set( 'Tab', function( data, cancel ) {
                    if ( editor.commands.get( 'indent' ) && editor.commands.get( 'indent' ).isEnabled ) {
                        editor.execute( 'indent' );
                    } else {
                        editor.model.change( function( writer ) {
                            editor.model.insertContent( writer.createText( '\u00A0\u00A0\u00A0\u00A0' ), editor.model.document.selection );
                        } );
                    }

                    cancel();
                } );

                editor.keystrokes.set( 'Shift+Tab', function( data, cancel ) {
                    if ( editor.commands.get( 'outdent' ) && editor.commands.get( 'outdent' ).isEnabled ) {
                        editor.execute( 'outdent' );
                        cancel();
                    }
                } );
			} )
			.catch( error => {
				console.error( 'Oops, something went wrong!' );
				console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
				console.warn( 'Build id: ham5y13wy58n-y439t0y9ehh' );
				console.error( error );
			} );      

    $(document).off('click.appointmentVariableInsert').on('click.appointmentVariableInsert', '.appointment-variable-insert', function () {
        var placeholder = $(this).data('placeholder');
        var editor = window.appointmentLetterEditor || window.editor;

        if (editor) {
            editor.model.change(function (writer) {
                editor.model.insertContent(writer.createText(placeholder), editor.model.document.selection);
            });
        }
    });

    $(document).off('click.appointmentVariableCopy').on('click.appointmentVariableCopy', '.appointment-variable-copy', function () {
        var placeholder = $(this).data('placeholder');

        copyAppointmentPlaceholder(placeholder);
    });

    function getAppointmentClauseBuilderPlaceholder() {
        var number = $('.appointment-clause-number-select').val() || '1';
        var alpha = $('.appointment-clause-alpha-select').val() || '';

        return '[[NO:' + number + ']]' + (alpha ? '[[ALP:' + alpha + ']]' : '');
    }

    function copyAppointmentPlaceholder(placeholder) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(placeholder);
            return;
        }

        var tempInput = document.createElement('textarea');
        tempInput.value = placeholder;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
    }

    $(document).off('click.appointmentClauseBuilderCopy').on('click.appointmentClauseBuilderCopy', '.appointment-clause-builder-copy', function () {
        copyAppointmentPlaceholder(getAppointmentClauseBuilderPlaceholder());
    });

    $(document).off('click.appointmentClauseBuilderInsert').on('click.appointmentClauseBuilderInsert', '.appointment-clause-builder-insert', function () {
        var placeholder = getAppointmentClauseBuilderPlaceholder();
        var editor = window.appointmentLetterEditor || window.editor;

        if (editor) {
            editor.model.change(function (writer) {
                editor.model.insertContent(writer.createText(placeholder), editor.model.document.selection);
            });
        }
    });
</script>
