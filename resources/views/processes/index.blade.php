@extends('layouts.layout')

@section('title')
	@php
	$title = __('Processes');
	$status = request()->get('status');
	if( $status === 'deleted'){
		$title = __('Process Archive');
	}
	@endphp
{{$title}}
@endsection

@section('sidebar')
@include('layouts.sidebar', ['sidebar'=> Menu::get('sidebar_processes')])
@endsection

@section('content')
@include('shared.breadcrumbs', ['routes' => [
    $title => null,
]])
<div class="container page-content" id="processIndex">
	<div class="row">
		<div class="col">
			<div class="input-group">
				<div class="input-group-prepend">
					<span class="input-group-text" id="basic-addon1">
						<i class="fas fa-search"></i>
					</span>
				</div>
				<input v-model="filter" class="form-control" placeholder="{{__('Search')}}...">
			</div>

		</div>
		<div class="col-8" align="right">
			@can('import-processes')
    			<a href="#" class="btn btn-outline-secondary" @click="goToImport"><i class="fas fa-file-import"></i>
    				{{__('Import')}}</a>
            @endcan
            @can('create-processes')
    			<a href="#" class="btn btn-secondary" data-toggle="modal" data-target="#addProcess"><i class="fas fa-plus"></i>
    				{{__('Process')}}</a>
            @endcan
		</div>
	</div>
	<div class="container-fluid">
		<processes-listing
            ref="processListing"
            :filter="filter"
            status="{{ $status }}"
            v-on:edit="edit"
            v-on:reload="reload"
            :permission="{{ \Auth::user()->hasPermissionsFor('processes') }}"
            ></processes-listing>
	</div>
</div>

@can('create-processes')
    <div class="modal" tabindex="-1" role="dialog" id="addProcess">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{__('Add A Process')}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="onClose">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" v-if="processCategories">
            <div class="form-group">
    			{!! Form::label('name', 'Name') !!}
    			{!! Form::text('name', null, ['autocomplete' => 'off', 'class'=> 'form-control', 'v-model'=> 'name', 'v-bind:class' => '{\'form-control\':true, \'is-invalid\':addError.name}']) !!}
    			<div class="invalid-feedback" v-for="name in addError.name">@{{name}}</div>
            </div>
            <div class="form-group">
    			{!! Form::label('description', 'Description') !!}
    			{!! Form::textarea('description', null, ['class'=> 'form-control', 'rows' => '3', 'v-model'=> 'description', 'v-bind:class' => '{\'form-control\':true, \'is-invalid\':addError.description}']) !!}
    			<div class="invalid-feedback" v-for="description in addError.description">@{{description}}</div>
            </div>
            <div class="form-group">
    			{!! Form::label('process_category_id', 'Category')!!}
    			{!! Form::select('process_category_id', [null => 'Category is required'] + $processCategories, null, ['class'=> 'form-control', 'v-model'=> 'process_category_id', 'v-bind:class' => '{\'form-control\':true, \'is-invalid\':addError.process_category_id}']) !!}
    			<div class="invalid-feedback" v-for="category in addError.process_category_id">@{{category}}</div>
            </div>
          </div>
            <div class="modal-body" v-else>
              <div>{{__('Categories are required to create a process')}}</div>
                <a  href="{{ url('processes/categories') }}" class="btn btn-primary container mt-2">
                         {{__('Add Category')}}
                </a>
            </div>
        	<div class="modal-footer">
    			<button type="button" class="btn btn-outline-secondary" data-dismiss="modal" v-if='processCategories' @click="onClose">{{__('Close')}}</button>
    			<button type="button" class="btn btn-secondary ml-2" @click="onSubmit" v-if='processCategories'>{{__('Save')}}</button>
            </div>
        </div>
        </div>
    </div>
@endcan
@endsection

@section('js')

@can('create-processes')
    <script>
        new Vue({
            el: '#addProcess',
            data: {
                name: '',
                categoryOptions: '',
                description: '',
                process_category_id: '',
                addError: {},
                status: '',
                processCategories: @json($processCategories)
            },
            methods: {
                onClose() {
                    this.name = '';
                    this.description = '';
                    this.process_category_id = '';
                    this.status = '';
                    this.addError = {};
                },
                onSubmit() {
                    this.errors = Object.assign({}, {
                        name: null,
                        description: null,
                        process_category_id: null,
                        status: null
                    });
                    if (this.process_category_id === '') {
                        this.addError = {"process_category_id":  ["Process Category is required"]};
                        ProcessMaker.alert('{{__('Process Category is required')}}', 'danger');
                    } else {
                        ProcessMaker.apiClient.post("/processes", {
                            name: this.name,
                            description: this.description,
                            process_category_id: this.process_category_id
                        })
                            .then(response => {
                                ProcessMaker.alert('{{__('Process successfully added')}}', 'success')
                                window.location = "/modeler/" + response.data.id
                            })
                            .catch(error => {
                                this.addError = error.response.data.errors;
                            })
                    }
                }
            }
        })
    </script>
@endcan

<script src="{{mix('js/processes/index.js')}}"></script>
@endsection
