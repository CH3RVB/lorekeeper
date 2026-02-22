@php
    $characters = \App\Models\Character\Character::visible(Auth::user() ?? null)
        ->myo(0)
        ->orderBy('slug', 'DESC')
        ->get()
        ->pluck('fullName', 'slug')
        ->toArray();
@endphp

<div id="characterComponents" class="hide">
    <div class="sales-character mb-3 card">
        <div class="card-body">
            <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
            <div class="row">
                <div class="col-md-2 align-items-stretch d-flex">
                    <div class="d-flex text-center align-items-center">
                        <div class="character-image-blank">Enter character code.</div>
                        <div class="character-image-loaded hide"></div>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="form-group">
                        {!! Form::label('slug', 'Character Code') !!}
                        {!! Form::select('slug[]', $characters, null, ['class' => 'form-control character-code', 'placeholder' => 'Select Character', 'placeholder' => 'Select Character']) !!}
                    </div>
                    <div class="character-details hide">
                        <h4>Sale Details</h4>

                        <div class="form-group mb-2">
                            {!! Form::label('Type') !!}
                            {!! Form::select('sale_type[]', ['flatsale' => 'Flatsale', 'auction' => 'Auction', 'ota' => 'OTA', 'xta' => 'XTA', 'raffle' => 'Raffle', 'flaffle' => 'Flatsale Raffle', 'pwyw' => 'Pay What You Want', 'raffle' => 'Free Raffle'], null, [
                                'class' => 'form-control character-sale-type',
                                'placeholder' => 'Select Sale Type',
                            ]) !!}
                        </div>

                        <div class="saleType">
                            <div class="mb-3 hide currencyOptions">
                                <h5>Currency Selection</h5>
                                <p>Please only select one type of currency, or it will default to IRL. IRL currency is any non-site currency such as euros, USD, or CAD.</p>
                                <div class="row">
                                    <div class="col form-group">
                                        {!! Form::label('Onsite Currency') !!}
                                        {!! Form::select('currency_id[]', $currencies, null, ['class' => 'form-control selectize currency_id', 'placeholder' => 'Select a Currency']) !!}
                                    </div>
                                    <div class="col form-group">
                                        {!! Form::label('IRL Currency') !!}
                                        {!! Form::text('irl_currency[]', null, ['class' => 'form-control irl_currency', 'placeholder' => 'Enter a Currency']) !!}
                                    </div>
                                </div>
                                <hr>
                                <h5>Recipient</h5>
                                <p>In some cases (like when hosting an auction on behalf of another user), you can designate a user that will earn any funds from this character's sale.</p>
                                <div class="col form-group">
                                    {!! Form::label('Recipient') !!}
                                    {!! Form::select('recipient_id[]', $userOptions, null, ['class' => 'form-control selectize recipient_id', 'placeholder' => 'Select a User']) !!}
                                </div>
                            </div>

                            <div class="mb-3 hide flatOptions">
                                <div class="form-group">
                                    {!! Form::label('Price') !!}
                                    {!! Form::number('price[]', null, ['class' => 'form-control', 'placeholder' => 'Enter a Cost']) !!}
                                </div>
                            </div>

                            <div class="mb-3 hide auctionOptions">
                                <div class="row">
                                    <div class="col form-group">
                                        {!! Form::label('Starting Bid') !!}
                                        {!! Form::number('starting_bid[]', null, ['class' => 'form-control', 'placeholder' => 'Enter a Starting Bid']) !!}
                                    </div>
                                    <div class="col form-group">
                                        {!! Form::label('Minimum Increment') !!}
                                        {!! Form::number('min_increment[]', null, ['class' => 'form-control', 'placeholder' => 'Enter a Minimum Increment']) !!}
                                    </div>
                                </div>
                                <h5>Snipe Guard</h5>
                                <p>Both must be set to enable a snipe guard. The snipe period is the number of minutes before the auction ends that the guard is in effect. The guard will extend the auction by that amount of minutes until no more bids
                                    take place. </p>
                                <div class="row">
                                    <div class="col form-group">
                                        {!! Form::label('Snipe Period') !!}
                                        {!! Form::number('snipe_period[]', null, ['class' => 'form-control', 'placeholder' => 'Enter a Time (in minutes)']) !!}
                                    </div>
                                    <div class="col form-group">
                                        {!! Form::label('Snipe Time') !!}
                                        {!! Form::number('snipe_guard[]', null, ['class' => 'form-control', 'placeholder' => 'Enter a Time (in minutes)']) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 hide xtaOptions">
                                <div class="form-group">
                                    {!! Form::label('Autobuy (Optional)') !!}
                                    {!! Form::number('autobuy[]', null, ['class' => 'form-control', 'placeholder' => 'Enter an Autobuy']) !!}
                                </div>
                            </div>

                            <div class="mb-3 hide pwywOptions">
                                <div class="form-group">
                                    {!! Form::label('Minimum Offer (Optional)') !!}
                                    {!! Form::number('minimum[]', null, ['class' => 'form-control', 'placeholder' => 'Enter a Minimum']) !!}
                                </div>
                            </div>

                            <div class="mb-3 hide endpointOptions">
                                <div class="form-group">
                                    {!! Form::label('End Point') !!}
                                    {!! Form::text('end_point[]', null, ['class' => 'form-control pre-datepicker', 'placeholder' => 'Provide a date for when the sale closes']) !!}
                                </div>
                            </div>

                        </div>

                        <div class="form-group my-2">
                            {!! Form::label('Notes (Optional)') !!}
                            {!! Form::text('description[]', null, ['class' => 'form-control', 'placeholder' => 'Provide any additional notes necessary']) !!}
                        </div>

                        <div class="form-group mb-4">
                            {!! Form::label('Link (Optional)') !!} {!! add_help('The URL for where to buy, bid, etc. on the character.') !!}
                            {!! Form::text('link[]', null, ['class' => 'form-control', 'placeholder' => 'URL']) !!}
                        </div>

                        {!! Form::checkbox('is_fto', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('is_fto', 'Is FTO Only', ['class' => 'form-check-label ml-3']) !!}

                        {!! Form::hidden('new_entry[]', 1) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
