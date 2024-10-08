@extends('admin.layout')

@section('admin-title')
    Items
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Items' => 'admin/data/items', ($item->id ? 'Edit' : 'Create') . ' Item' => $item->id ? 'admin/data/items/edit/' . $item->id : 'admin/data/items/create']) !!}

    <h1>{{ $item->id ? 'Edit' : 'Create' }} Item
        @if ($item->id)
            <a href="#" class="btn btn-outline-danger float-right delete-item-button">Delete Item</a>
        @endif
    </h1>

    {!! Form::open(['url' => $item->id ? 'admin/data/items/edit/' . $item->id : 'admin/data/items/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $item->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($item->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Item Category (Optional)') !!}
                {!! Form::select('item_category_id', $categories, $item->item_category_id, ['class' => 'form-control']) !!}
            </div>
        </div>
        @if (Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('Item Rarity (Optional)') !!} {!! add_help('This should be a number.') !!}
                    {!! Form::number('rarity', $item && $item->rarity ? $item->rarity : '', ['class' => 'form-control']) !!}
                </div>
            </div>
        @endif
    </div>

    @if (Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('Reference Link (Optional)') !!} {!! add_help('An optional link to an additional reference') !!}
                    {!! Form::text('reference_url', $item->reference_url, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md">
                {!! Form::label('Item Artist (Optional)') !!} {!! add_help('Provide the artist\'s username if they are on site or, failing that, a link.') !!}
                <div class="row">
                    <div class="col-md">
                        <div class="form-group">
                            {!! Form::select('artist_id', $userOptions, $item && $item->artist_id ? $item->artist_id : null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select a User']) !!}
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-group">
                            {!! Form::text('artist_url', $item && $item->artist_url ? $item->artist_url : '', ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $item->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    @if (Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
        <div class="form-group">
            {!! Form::label('Uses (Optional)') !!} {!! add_help('A short description of the item\'s use(s). Supports raw HTML if need be, but keep it brief.') !!}
            {!! Form::text('uses', $item && $item->uses ? $item->uses : '', ['class' => 'form-control']) !!}
        </div>
    @endif

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('allow_transfer', 1, $item->id ? $item->allow_transfer : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this item to other users. Non-account-bound items can be account-bound when granted to users directly.') !!}
        </div>
        @if (Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
            <div class="col-md form-group">
                {!! Form::checkbox('is_released', 1, $item->id ? $item->is_released : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_released', 'Is Released', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to view information for the item/it will be hidden from view. This is overridden by the item being owned at any point by anyone on the site.') !!}
            </div>
        @endif
    </div>

    @if (Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
        <h3>Availability Information</h3>
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('release', 'Source (Optional)') !!} {!! add_help('The original and/or general source of the item. Should be brief.') !!}
                    {!! Form::text('release', $item && $item->source ? $item->source : '', ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('prompts[]', 'Drop Location(s) (Optional)') !!} {!! add_help('You can select up to 10 prompts at once.') !!}
                    {!! Form::select('prompts[]', $prompts, $item && isset($item->data['prompts']) ? $item->data['prompts'] : '', ['id' => 'promptsList', 'class' => 'form-control', 'multiple']) !!}
                </div>
            </div>
        </div>
    @endif

    @if (Config::get('lorekeeper.extensions.item_entry_expansion.resale_function'))
        <h3>Resale Information</h3>
        <p>The currency and amount users will be able to sell this item from their inventory for. If quantity is not set, the item will be unable to be sold.</p>
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('currency_id', 'Currency') !!}
                    {!! Form::select('currency_id', $userCurrencies, isset($item->data['resell']) && App\Models\Currency\Currency::where('id', $item->resell->flip()->pop())->first() ? $item->resell->flip()->pop() : null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('currency_quantity', 'Quantity') !!}
                    {!! Form::text('currency_quantity', isset($item->data['resell']) ? $item->resell->pop() : null, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
    @endif

    <h3>Expiry</h3>
    <div class="row">
        <div class="col">
            <p>You can set this item to "expire", which means users that own it will have it automatically removed from their inventory after the time is up.</p>
            <p>You can set a global date, or set it so that the item only expires a certain time after each specific user obtains this item.</p>
            <p>If you're swapping between the different types of expiry here, make sure to delete the old data for the type you don't want to use anymore, or the automatic wiping will not function due to data conflicts. (Delete the global expiry date if
                you want to use per-person expiry, and vice versa)</p>
        </div>
        <div class="col form-group">
            {!! Form::checkbox('personal_only', 1, $item->expiry_number ? 1 : 0, ['class' => 'form-check-label', 'data-toggle' => 'toggle', 'id' => 'personal']) !!}
            {!! Form::label('personal_only', 'Personal Expiry Only', ['class' => 'form-check-label ml-3']) !!}{!! add_help('Toggle on the the per-user expiry instead of global only.') !!}
        </div>
    </div>
    <div class="form-group personal_only {{ $item->expiry_number ? 'hide' : '' }}">
        <h5>Global Expiry</h5>
        <p>A global time means that all copies of this item, regardless of when they have been gotten, will be deleted after this date passes.</p>
        {!! Form::label('expiry_date', 'Expiry Date') !!} {!! add_help('The date at which all copies of this item will expire.') !!}
        {!! Form::text('expiry_date', $item->expiry_date, ['class' => 'form-control datepicker']) !!}
    </div>
    <div class="personal {{ $item->expiry_number ? '' : 'hide' }}">
        <h5>Per-person Expiry</h5>
        <p>A per-person expiry means that a user's items will only expire relative to when they have obtained this item. For example, 2 months after they have gotten it, etc. <strong>Both parameters must be set when using this option.</strong></p>
        <p><strong>Note that a user giving an item to a character has the potential to "refresh" this timer in certain cases, so be cautious, and potentially disable character-owning on this item as a preventive measture.</strong></p>
        <p>This extension will delete any items held by characters, as well, but this may still cause problems with timer refreshing.</p>
        {!! Form::label('expiry_number', 'Personal Expiry') !!} {!! add_help('The number works in combination with the timeframe, so 3 hours, or 10 days, or 3 months, etc, after the user gets the item.') !!}
        <div class="d-flex my-2">
            {!! Form::number('expiry_number', $item->expiry_number, ['class' => 'form-control mr-2', 'placeholder' => 'Expiry Time']) !!}
            {!! Form::select('expiry_interval', ['Hour' => 'Hour', 'Day' => 'Day', 'Month' => 'Month', 'Year' => 'Year'], $item->expiry_interval, [
                'class' => 'form-control mr-2 default',
                'placeholder' => 'Expiry Interval',
            ]) !!}
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($item->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($item->id)
        <h3>Item Tags</h3>
        <p>Item tags indicate extra functionality for the item. Click on the edit button to edit the specific item tag's data.</p>
        @if (count($item->tags))
            <table class="table">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Active?</th>
                        <th></th>
                    </tr>
                </thead>
                @foreach ($item->tags as $tag)
                    <tr>
                        <td>{!! $tag->displayTag !!}</td>
                        <td class="{{ $tag->is_active ? 'text-success' : 'text-danger' }}">{{ $tag->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-right"><a href="{{ url('admin/data/items/tag/' . $item->id . '/' . $tag->tag) }}" class="btn btn-outline-primary">Edit</a></td>
                    </tr>
                @endforeach
            </table>
        @else
            <p>No item tags attached to this item.</p>
        @endif
        <div class="text-right">
            <a href="{{ url('admin/data/items/tag/' . $item->id) }}" class="btn btn-outline-primary">Add a Tag</a>
        </div>

        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._item_entry', ['imageUrl' => $item->imageUrl, 'name' => $item->displayName, 'description' => $item->parsed_description, 'searchUrl' => $item->searchUrl])
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('#promptsList').selectize({
                maxItems: 10
            });

            $('.delete-item-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/items/delete') }}/{{ $item->id }}", 'Delete Item');
            });

            $(".datepicker").datetimepicker({
                dateFormat: "yy-mm-dd",
                timeFormat: 'HH:mm:ss',
            });
            $('#personal').change(function() {
                if ($(this).is(':checked')) {
                    $('.personal').removeClass('hide');
                    $('.personal_only').addClass('hide');
                } else {
                    $('.personal').addClass('hide');
                    $('.personal_only').removeClass('hide');
                }
            });
        });
    </script>
@endsection
