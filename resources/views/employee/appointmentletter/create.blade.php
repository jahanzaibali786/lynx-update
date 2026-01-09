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
		
				
				
				
		
				
				
				
			} )
			.catch( error => {
				console.error( 'Oops, something went wrong!' );
				console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
				console.warn( 'Build id: ham5y13wy58n-y439t0y9ehh' );
				console.error( error );
			} );      
</script>