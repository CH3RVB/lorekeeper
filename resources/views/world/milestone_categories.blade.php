@if ($create)
    {!! Form::label('Milestone Category (Optional)') !!}{!! add_help('If there is a category set, this milestone will check only for items the user owns that are part of this category.') !!}
@endif
{!! Form::select('category_id', $categories, $preset, ['class' => 'form-control']) !!}
