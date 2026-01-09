{{ Form::model($letter, ['route' => ['appointment-letter-update', $letter->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}

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
        {{-- //letter date  --}}

        <div class="form-group col-md-4">
            {!! Form::label('date', __('Date'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
            {!! Form::date('date',old('date'), ['class' => 'form-control']) !!}
        </div>
    </div>
    <div id="editor-container">
        <div id="toolbar-container"></div>
        {{-- <div id="editor">{!! old('datacontent', $letter->datacontent) !!}</div> --}}
        <textarea name="datacontent" class="editor">{!! old('datacontent', $letter->datacontent) !!}</textarea>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-outline-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn btn-outline-primary">
    </div>
</div>
{{ Form::close() }}

<!-- <script>
    DecoupledEditor
        .create(document.querySelector('#editor'), {
            toolbar: {
                items: [
                    'heading', '|', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor','|',
                    'bold', 'italic','subscript', 'superscript', 'code', 'underline', 'strikethrough', 'link', 'alignment','|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'outdent', 'indent', '|',
                    'imageUpload', 'blockQuote',  'codeBlock','insertTable', 'mediaEmbed', '|',
                    'undo', 'redo','|', 'selectAll','|', 'accessibilityHelp','Essentials'
                ]
            },
            language: 'en',
            image: {
                toolbar: [
                    'imageTextAlternative', 'imageStyle:full', 'imageStyle:side'
                ]
            },
            table: {
                contentToolbar: [
                    'tableColumn', 'tableRow', 'mergeTableCells'
                ]
            }
        })
        .then(editor => {
            const toolbarContainer = document.querySelector('#toolbar-container');
            toolbarContainer.appendChild(editor.ui.view.toolbar.element);

            editor.model.document.on('change:data', () => {
                document.querySelector('#hidden-content').value = editor.getData();
            });
        })
        .catch(error => {
            console.error('There was a problem initializing the editor.', error);
        });
</script> -->
{{-- <script>
        CKEDITOR.replace('editor', {
            customConfig: '', 
            toolbar: [
                { name: 'document', items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
                { name: 'clipboard', items: ['Undo', 'Redo', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'RemoveFormat'] },
                { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'] },
                { name: 'forms', items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'] },
                '/',
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-', 'Strike', '-', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                { name: 'insert', items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak'] },
                '/',
                { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'tools', items: ['Maximize', 'ShowBlocks'] },
                { name: 'about', items: ['About'] }
            ],
            extraPlugins: 'uploadimage',
            removePlugins: 'elementspath',
            resize_enabled: true,
            height: 350,
            on: {
                instanceReady: function(evt) {
                    const toolbarContainer = document.getElementById('toolbar-container');
                    const toolbar = evt.editor.container.$.querySelector('.cke_top');
                    toolbarContainer.appendChild(toolbar);
                },
                change: function(evt) {
                    document.getElementById('hidden-content').value = evt.editor.getData();
                }
            }
        });
    </script> --}}
    <script src="{{ asset('js/ckeditor.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/45.2.1/translations/en.umd.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5-premium-features/45.2.1/translations/en.umd.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('.editor'), {
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
		
				
				
				
		
				
				
				
			} )
			.catch( error => {
				console.error( 'Oops, something went wrong!' );
                    console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
				console.warn( 'Build id: ham5y13wy58n-y439t0y9ehh' );
				console.error( error );
			} );      
</script>