<div class="card-header h2">User Sales</div>
<div class="card-body">
    <p>You will be able to create and submit a character sale/raffle post for approval, similar to official onsite sales.</p>
</div>

@if ((isset($staffView) && $staffView) || !$submission->id || $submission->status == 'Draft')
    <h3>Sale Information</h3>
    <p>After your post has been approved, you will not be able to modify it, only delete it.</p>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Title') !!}
            {!! Form::text('title', isset($data['title']) ? $data['title'] : null, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Post Content') !!} {!! add_help('Please provide information about the sale such as what you are seeking, etc.') !!}
        {!! Form::textarea('text', isset($data['text']) ? $data['text'] : null, ['class' => 'form-control wysiwyg']) !!}
    </div>
@else
    <h3>Sale Information</h3>
    <hr>
    <div class="mb-1">
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Title</h5>
            </div>
            <div class="col-md-10 col-8">{{ $data['title'] }}</div>
        </div>
    </div>

    <h5>Description</h5>
    {!! isset($data['text']) ? $data['parsed_text'] : '<p>None given.</p>' !!}
@endif
