<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .form-title {
            text-align: center;
            font-weight: bold;
            padding: 10px 0;
            font-size: 16px;
            background-color: #9c9b9b;
            margin-bottom: 30px;
        }

        .form-section {
            margin: 10px 0;
        }

        .form-row {
            margin-bottom: 10px;
        }

        .form-row:after {
            content: "";
            display: table;
            clear: both;
        }

        .row1 .form-field {
            float: left;
            width: 290px;
            border-bottom: 1px solid #000;
        }

        .row2 .form-field {
            float: left;
            width: 160px;
            border-bottom: 1px solid #000 !important;
        }

        .row3 .form-field {
            float: left;
            width: 130px;
            border-bottom: 1px solid #000 !important;
        }

        .form-label {
            float: left;
            width: fit-content;
            font-weight: bold;
        }
        table,th,td,tr,tbody,thead{
            border: 1px solid #000 !important;
            border-collapse: collapse !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.6rem;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            border-collapse: collapse;
        }
        tr{
            border: 1px solid #000;
            border-collapse: collapse;
        }
        th{
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .table-title {
            text-align: center;
            font-weight: bold;
            padding: 5px 0;
            font-size: 14px;
        }

        .tick-note {
            text-align: right;
            font-size: 12px;
            font-style: italic;
        }

        .key-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .key-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }

        .key-label {
            font-weight: bold;
            text-align: left;
            padding: 5px;
        }

        .remarks-section {
            margin-top: 10px;
        }

        .remarks-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .remarks-lines {
            border-bottom: 1px solid #000;
            height: 25px;
            margin-bottom: 5px;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 40%;
            display: inline-block;
        }

        .signature-label {
            font-weight: bold;
            font-size: 12px;
        }

        .school-office-section {
            margin-top: 30px;
        }

        .section-header {
            background-color: #f2f2f2;
            text-align: center;
            padding: 5px;
            font-weight: bold;
            border: 1px solid #000;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .salary-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .salary-label {
            font-size: 0.6rem;
        }

        .salary-field {
            height: 15px;
        }

        .branch-stamp-section {
            margin-top: 30px;
        }

        .stamp-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }

        .stamp-line {
            display: inline-block;
            width: 300px;
            border-bottom: 1px solid #000;
        }

        .accountant-signature-section {
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .head-office-section {
            /* margin-top: 20px;
             */
        }

        .approved-salary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .approved-salary-table td {
            padding: 5px;
            vertical-align: top;
        }

        .salary-column {
            width: 30%;
        }

        .remarks-column {
            width: 70%;
        }

        .header-cell {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            padding: 5px;
        }

        .final-signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .manager-section {
            width: 40%;
        }

        .director-section {
            width: 40%;
            text-align: right;
        }

        .wef-section {
            margin-top: 20px;
        }

        .wef-label {
            font-weight: bold;
            display: inline-block;
            width: 50px;
            text-align: right;
            padding-right: 10px;
        }

        .date-fixed {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 150px;
            text-align: center;
        }
        
        .employee-assessment {
            width: 50%;
            float: right;
            margin-top: 20px;
        }
        
        .assessment-field {
            border-bottom: 1px solid #000;
            height: 25px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="form-title">Teacher APPRAISAL FORM "PERIOD 2018"</div>

    <div class="form-section">
        <div class="form-row row1">
            <div class="form-label">Branch Name</div>
            <div class="form-field">&nbsp;</div>
            <div class="form-label">DATE:</div>
            <div class="form-field">&nbsp;</div>
        </div>

        <div class="form-row row2" style="clear: both; margin-top: 30px;">
            <div class="form-label">Name of Employee</div>
            <div class="form-field">&nbsp;</div>

            <div class="form-label">Designation</div>
            <div class="form-field">&nbsp;</div>

            <div class="form-label">D/O/J:</div>
            <div class="form-field">&nbsp;</div>
        </div>

        <div class="form-row row3" style="clear: both; margin-top: 30px;">
            <div class="form-label">Qualification</div>
            <div class="form-field">&nbsp;</div>

            <div class="form-label">Professional Qualification</div>
            <div class="form-field">&nbsp;</div>

            <div class="form-label">Service Period</div>
            <div class="form-field">&nbsp;</div>
        </div>
    </div>

    <div class="tick-note">Tick appropriate</div>

    <div class="table-title">PART-A (PERSONAL)</div>
    <table class="datatable">
        <tr>
            <th width="5%">Sr#</th>
            <th width="15%"></th>
            <th width="35%">Areas</th>
            <th width="10%">1</th>
            <th width="10%">2</th>
            <th width="10%">3</th>
            <th width="15%">Total</th>
        </tr>
        <tr>
            <td>1</td>
            <td rowspan="2"></td>
            <td>Holds self and other accountable</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Manages Resources responsibly in compliance with regulation</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>3</td>
            <td rowspan="2">Accountability</td>
            <td>Accepts responsibility in competent manner for own work and of subordinates</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>4</td>
            <td>Works collaboratively with partners</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>5</td>
            <td rowspan="4">Communications</td>
            <td>Exhibits effective listening oral and written skills</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>6</td>
            <td>Facilitates open exchange of ideas</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>7</td>
            <td>Skillfully settles differences by using win-win approach</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>8</td>
            <td>Is committed to innovation and continuous improvement</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>9</td>
            <td rowspan="4">Managing Change</td>
            <td>Recognizes the need for change and takes calculated risks</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>10</td>
            <td>Deals effectively with resistance to change</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>11</td>
            <td>Generates enthusiasm and commitment in staff to embrace change</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>12</td>
            <td>Identifies and recruits team commitment and motivates team members</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>13</td>
            <td rowspan="3">Managing People</td>
            <td>Gives opportunity and feedback to staff to increase their abilities and potential</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>14</td>
            <td>Creates conducive environment for staff</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>15</td>
            <td>Encourages and accepts differences of opinion</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>16</td>
            <td rowspan="2"></td>
            <td>Manages difficult people effectively</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>17</td>
            <td>Upholds the core value of integrity and honesty</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>18</td>
            <td rowspan="3">Managing Self</td>
            <td>Positively influences others to adopt and maintain highest standards</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>19</td>
            <td >Practices proactive time management in setting workload and taking decisions</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>20</td>
            <td>Inspires respect and trust</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>21</td>
            <td rowspan="3">Strategic Thinking</td>
            <td>Shares strategic vision and influence others towards its realization</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>22</td>
            <td>Is result oriented and makes things happen</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>23</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <div class="table-title">Part-C (Professional Attributes)</div>
    <table class="datatable">
        <tr>
            <th width="5%">Sr#</th>
            <th width="20%">Areas</th>
            <th width="35%"></th>
            <th width="10%">1</th>
            <th width="10%">2</th>
            <th width="20%">Total</th>
        </tr>
        <tr>
            <td>1</td>
            <td rowspan="2">Academics</td>
            <td>Curriculum Implementation</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Academic Progress/result & growth</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>1</td>
            <td rowspan="4">Others</td>
            <td>Regularity & Punctuality</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Vigilance/Sense of responsibility</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>3</td>
            <td>Loyalty</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>4</td>
            <td>Marketing</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <div class="table-title">FOR STATISTICS AND DATA (TO BE FILLED BY BRANCH AND VERIFIED BY ACCOUNTS DEPARTMENT)</div>
    <table class="datatable">
        <tr>
            <th width="5%">Sr#</th>
            <th width="20%"></th>
            <th width="35%"></th>
            <th width="20%"></th>
            <th width="20%"></th>
        </tr>
        <tr>
            <td>1</td>
            <td rowspan="4">Financial</td>
            <td>Total No. Admission</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Total No. of Withdrawals</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>3</td>
            <td>Branch Fee Concession</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>4</td>
            <td>Fee Debts</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>5</td>
            <td></td>
            <td>Professional development during year</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="key-table">
        <tr>
            <td class="key-label" width="8%">Key:</td>
            <td width="18%">90-99% (A+)</td>
            <td width="18%">80-89% (A)</td>
            <td width="18%">70-79% (B+)</td>
            <td width="18%">60-69% (B)</td>
            <td width="10%">ACR Grade</td>
            <td width="10%"></td>
        </tr>
    </table>

    <!-- Head's Remarks Section -->
    <div class="remarks-section">
        <div class="remarks-title">MANAGING DIRECTOR REMARKS:</div>
        <div class="remarks-lines">&nbsp;</div>
        <div class="remarks-lines">&nbsp;</div>
        <div class="remarks-lines">&nbsp;</div>
        <div class="remarks-lines">&nbsp;</div>
        <div class="remarks-lines">&nbsp;</div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div>
            <span class="signature-label">Employee's Signature</span>
            <div class="signature-line">&nbsp;</div>
        </div>
        <br><br>
        <div>
            <span class="signature-label">Head of institution</span>
            <div class="signature-line">&nbsp;</div>
        </div>
    </div>

    <!-- School Office Section -->
    <div class="school-office-section">
        <div class="section-header">TO BE FILLED BY SCHOOL OFFICE</div>
        <table class="salary-table">
            <tr>
                <td width="50%" colspan="2" style="text-align: center; font-weight: bold;">A<br>CURRENT SALARY
                    STATUS</td>
                <td width="50%" style="text-align: center; font-weight: bold;" rowspan="9">
                    <div style="text-align: left; font-weight: bold;">Employee's Self Assessment</div>
                    <div class="assessment-field">&nbsp;</div>
                    <div class="assessment-field">&nbsp;</div>
                    <div class="assessment-field">&nbsp;</div>
                    <div class="assessment-field">&nbsp;</div>
                    <div class="assessment-field">&nbsp;</div>
                    <div class="assessment-field">&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="salary-label">P.SCALE</div>
                    <div class="salary-field">&nbsp;</div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="salary-label">GROSS SALARY</div>
                    <div class="salary-field">&nbsp;</div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="salary-label">PESSI EMPLOYER</div>
                    <div class="salary-field">&nbsp;</div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="salary-label">EOBI EMPLOYER</div>
                    <div class="salary-field">&nbsp;</div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="salary-label">CHILD CONCESSION</div>
                    <div class="salary-field">&nbsp;</div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="salary-label">OTHER</div>
                    <div class="salary-field">&nbsp;</div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="salary-label">TOTAL COST TO COMPANY</div>
                    <div class="salary-field">&nbsp;</div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="salary-label">NET SALARY</div>
                    <div class="salary-field">&nbsp;</div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    
    <!-- Branch Stamp Section -->
    <div class="branch-stamp-section">
        <div class="stamp-label">Branch Stamp:</div>
        <div class="stamp-line">&nbsp;</div>
    </div>

    <!-- Accountant and Head Signature Section -->
    <div class="accountant-signature-section">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td width="50%">
                    <div class="signature-label">Branch Accountant Signature</div>
                </td>
                <td width="50%">
                    <div class="signature-label">Head of Institution</div>
                </td>
            </tr>
        </table>
    </div>
<br>
    <!-- Head Office Section -->
    <div class="head-office-section">
        <div class="section-header">TO BE FILLED BY HEAD OFFICE</div>
        <table class="approved-salary-table" cellspacing="0" cellpadding="0">
            <tr>
                <td class="salary-column" valign="top">
                    <div class="header-cell">APPROVED SALARY STATUS</div>
                    <div style="padding: 5px;">
                        <div class="salary-label">P.SCALE</div>
                        <div class="salary-field">&nbsp;</div>

                        <div class="salary-label">P.G.SALARY</div>
                        <div class="salary-field">&nbsp;</div>

                        <div class="salary-label">PESSI EMPLOYER</div>
                        <div class="salary-field">&nbsp;</div>

                        <div class="salary-label">EOBI EMPLOYER</div>
                        <div class="salary-field">&nbsp;</div>

                        <div class="salary-label">CHILD CONCESSION</div>
                        <div class="salary-field">&nbsp;</div>

                        <div class="salary-label">OTHER</div>
                        <div class="salary-field">&nbsp;</div>

                        <div class="salary-label">TOTAL COST TO COMPANY</div>
                        <div class="salary-field">&nbsp;</div>

                        <div class="salary-label">NET SALARY</div>
                        <div class="salary-field">&nbsp;</div>
                    </div>
                </td>
                <td class="remarks-column" valign="top">
                    <div class="header-cell">REMARKS</div>
                    <div class="remarks-lines">&nbsp;</div>
                    <div class="remarks-lines">&nbsp;</div>
                    <div class="remarks-lines">&nbsp;</div>
                    <div class="remarks-lines">&nbsp;</div>
                    <div class="remarks-lines">&nbsp;</div>
                    <div class="remarks-lines">&nbsp;</div>
                    <div class="remarks-lines">&nbsp;</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Final Signatures Section -->
    <div class="accountant-signature-section">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td width="33%">
                    <div class="signature-label">Manager Finance</div>
                </td>
                <td width="33%">
                    <div class="signature-label">W.E.F</div>
                    <span>_________</span>
                    <span style="border-bottom: 1px solid #000;">1-Jul-18</span>
                </td>
                <td width="33%" style="text-align: right;">
                    <div class="signature-label">Approved by Managing Director</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>