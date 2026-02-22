@extends('user_sales.layout')

@section('user_sales-title')
    {{ $sales->id ? 'Edit' : 'Create' }} Sales Post
@endsection

@section('user_sales-content')
    {!! breadcrumbs(['User Sales' => 'user-sales', ($sales->id ? 'Edit' : 'Create') . ' Post' => $sales->id ? 'user-sales/edit/' . $sales->id : 'user-sales/create']) !!}

    <h1>{{ $sales->id ? 'Edit' : 'Create' }} Sales Post
        @if ($sales->id)
            <a href="#" class="btn btn-danger float-right delete-sales-button">Delete Post</a>
            <a href="{{ $sales->url }}" class="btn btn-info float-right mr-md-2">View Post</a>
        @endif
    </h1>

    {!! Form::open(['url' => $sales->id ? 'user-sales/edit/' . $sales->id : 'user-sales/create', 'id' => 'submissionForm']) !!}

    <h3>Basic Information</h3>
    <p>On this page you can create a sales post in which you can offer your characters for currency.</p>
    <p>Once your post has been created, you must submit it for approval by moderators, during and after which you will not be able to modify it.</p>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Title') !!}
            {!! Form::text('title', $sales->title, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Post Content') !!} {!! add_help('Please provide information about the sale such as what you are seeking, etc.') !!}
        {!! Form::textarea('text', $sales->text, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <h3>Characters</h3>
    <p>
        Add characters to be featured in this sales post.
    </p>

    <div id="characters" class="mb-3">
        @if ($sales->id)
            @if (count($sales->characters()->whereRelation('character', 'deleted_at', null)->get()) != count($sales->characters))
                <div class="alert alert-warning">
                    <strong>Warning!</strong> Some characters have been deleted since they were added to this post. Editing this post will remove those characters permanently from the post.
                </div>
            @endif
            @foreach ($sales->characters()->whereRelation('character', 'deleted_at', null)->get() as $character)
                @include('user_sales._character_select_entry', ['character' => $character])
            @endforeach
        @endif
    </div>
    <div class="text-right mb-3">
        <a href="#" class="btn btn-outline-info" id="addCharacter">Add Character</a>
    </div>

    <div class="text-right">
        {!! Form::submit($sales->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
        @if ($sales->id)
            <a href="#" class="btn btn-success" id="approvalButton">Finalize Sales</a>
        @endif
    </div>

    {!! Form::close() !!}

    @include('user_sales._character_select')

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content hide" id="approvalContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Finalization</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>After this post is finalized, it will enter the approval queue. It will not be able to be modified thereafter.</p>
                    <p class="text-danger">Are you sure you wish to finalize this post?</p>
                    <div class="text-right">
                        <a href="#" id="approvalSubmit" class="btn btn-success">Finalize and Submit</a>
                    </div>
                </div>
            </div>
            <div class="modal-content hide" id="cancelContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Cancellation</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will cancel the submission and send it back to drafts. Make sure to include a staff comment if you do this!</p>
                    <div class="text-right">
                        <a href="#" id="cancelSubmit" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
            <div class="modal-content hide" id="rejectionContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Rejection</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will reject the submission.</p>
                    <div class="text-right">
                        <a href="#" id="rejectionSubmit" class="btn btn-danger">Reject</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    @include('user_sales._character_select_js')
    <script>
        $(document).ready(function() {
            $('.delete-sales-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('user-sales/delete') }}/{{ $sales->id }}", 'Delete Post');
            });
            $(".datepicker").datetimepicker({
                dateFormat: "yy-mm-dd",
                timeFormat: 'HH:mm:ss',
            });

            var $confirmationModal = $('#confirmationModal');
            var $submissionForm = $('#submissionForm');

            var $approvalButton = $('#approvalButton');
            var $approvalContent = $('#approvalContent');
            var $approvalSubmit = $('#approvalSubmit');


            $approvalButton.on('click', function(e) {
                e.preventDefault();
                $approvalContent.removeClass('hide');
                $confirmationModal.modal('show');
            });

            $approvalSubmit.on('click', function(e) {
                e.preventDefault();
                $submissionForm.submit();
            });

        });
    </script>
@endsection
