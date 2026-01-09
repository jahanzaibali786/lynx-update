{{Form::model($termination,array('route' => array('termination.update', $termination->id), 'method' => 'PUT')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group  col-lg-6 col-md-6">
            {{ Form::label('employee_id', __('Employee'),['class'=>'form-label'])}}
            {{ Form::select('employee_id', $employees,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-lg-6 col-md-6">
            {{ Form::label('termination_type', __('Termination Type'),['class'=>'form-label']) }}
            {{ Form::select('termination_type', $terminationtypes,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-lg-6 col-md-6">
            {{Form::label('notice_date',__('Notice Date'),['class'=>'form-label'])}}
            {{Form::date('notice_date',null,array('class'=>'form-control '))}}
        </div>
        <div class="form-group  col-lg-6 col-md-6">
            {{Form::label('termination_date',__('Termination Date'),['class'=>'form-label'])}}
            {{Form::date('termination_date',null,array('class'=>'form-control '))}}
        </div>
 <div id="editor-container" class="form-group col-lg-12 col-md-12">
        {{-- <div id="editor" class="editor"></div> --}}
        <textarea name="description" class="editor">{{ @$termination->description }}</textarea>
    </div>       
    
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
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>

    {{Form::close()}}
