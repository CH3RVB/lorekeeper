<div class="sales-character-entry mb-3 card">
    <div class="card-body">
        <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
        <div class="row">
            <div class="col-md-2 align-items-stretch d-flex">
                <div class="d-flex text-center align-items-center">
                    <div class="character-image-blank hide">Enter character code.</div>
                    <div class="character-image-loaded">
                        @include('home.queues._character', ['character' => $character->character ? $character->character : $character])
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('slug[]', 'Character Code') !!}
                    {!! Form::select('slug[]', $charSelect, $character->character ? $character->character->slug : $character->slug, ['class' => 'form-control character-code', 'placeholder' => 'Select Character']) !!}
                </div>
                <div class="character-details">
                    <h4>Sale Details</h4>

                    <div class="form-group mb-2">
                        {!! Form::label('Type') !!}
                        {!! Form::select('sale_type[' . $character->character_id . ']', ['flatsale' => 'Flatsale', 'ota' => 'OTA', 'xta' => 'XTA', 'raffle' => 'Free Raffle', 'pwyw' => 'Pay What You Want'], $data['type'] ?? null, [
                            'class' => 'form-control character-sale-type',
                            'placeholder' => 'Select Sale Type',
                        ]) !!}
                    </div>

                    <div class="saleType">
                        <div class="mb-3 {{ isset($data['type'])  != 'raffle' ? 'show' : 'hide' }} currencyOptions">
                            <h5>Currency Selection</h5>
                            <p>Please only select one type of currency, or it will default to IRL.</p>
                            <p>IRL Currency is any non-site currency such as euros, USD, or CAD.</p>
                            <p>Characters that cannot be sold <strong>cannot</strong> be offered for sale, but they can be raffled or offered in OTA/XTA, provided that you do not specify currency.</p>
                            <div class="row">
                                <div class="form-group">
                                    {!! Form::label('Onsite Currency') !!}
                                    {!! Form::select('currency_id[' . $character->character_id . ']', $currencies, isset($data['data']['currency_id']) ? $data['data']['currency_id'] : null, ['class' => 'form-control currency_id', 'placeholder' => 'Select a Currency']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('IRL Currency') !!}
                                    {!! Form::text('irl_currency[' . $character->character_id . ']', isset($data['data']['irl_currency']) ? $data['data']['irl_currency'] : null, ['class' => 'form-control irl_currency', 'placeholder' => 'Enter a Currency']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 {{ isset($data['type']) == 'flatsale' ? 'show' : 'hide' }} flatOptions">
                            <h5>Flatsale Price</h5>
                            <p>For flatsales, a price is <strong>required.</strong></p>
                            <div class="form-group">
                                {!! Form::label('Price') !!}
                                {!! Form::number('price[' . $character->character_id . ']', isset($data['data']['price']) ? $data['data']['price'] : null, ['class' => 'form-control price', 'placeholder' => 'Enter a Cost']) !!}
                            </div>
                        </div>

                        <div class="mb-3 {{ isset($data['type']) == 'xta' || isset($data['type']) == 'ota' ? 'show' : 'hide' }} xtaOptions">
                            <div class="form-group">
                                {!! Form::label('Autobuy') !!}
                                {!! Form::number('autobuy[' . $character->character_id . ']', isset($data['data']['autobuy']) ? $data['data']['autobuy'] : null, ['class' => 'form-control autobuy', 'placeholder' => 'Enter an Autobuy']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('End Point (Optional)') !!}
                                {!! Form::text('end_point[' . $character->character_id . ']', isset($data['data']['end_point']) ? $data['data']['end_point'] : null, ['class' => 'form-control end_point', 'placeholder' => 'Provide information about when offers close']) !!}
                            </div>
                        </div>

                        <div class="mb-3 {{ isset($data['type']) == 'pwyw' || isset($data['type']) == 'ota' || isset($data['type']) == 'xta' ? 'show' : 'hide' }} pwywOptions">

                            <div class="form-group">
                                {!! Form::label('Minimum Offer') !!}
                                {!! Form::number('minimum[' . $character->character_id . ']', isset($data['data']['minimum']) ? $data['data']['minimum'] : null, ['class' => 'form-control minimum', 'placeholder' => 'Enter a Minimum']) !!}
                            </div>
                        </div>
                        <div class="mb-3 {{ isset($data['type']) == 'raffle' ? 'show' : 'hide' }} raffleOptions">
                            <div class="form-group">
                                {!! Form::label('End Point') !!}
                                {!! Form::text('end_point[' . $character->character_id . ']', isset($data['data']['end_point']) ? $data['data']['end_point'] : null, [
                                    'class' => 'form-control post-datepicker end_point',
                                    'placeholder' => 'Provide a date for when the raffle closes',
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        {!! Form::label('Notes (Optional)') !!}
                        {!! Form::text('description[' . $character->character_id . ']', isset($data['description']) ? $data['description'] : null, ['class' => 'form-control description', 'placeholder' => 'Provide any additional notes necessary']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
