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
                        {!! Form::select('slug[]', $charSelect, null, ['class' => 'form-control character-code', 'placeholder' => 'Select Character', 'placeholder' => 'Select Character']) !!}
                    </div>
                    <div class="character-details hide">
                        <h4>Sale Details</h4>

                        <div class="form-group mb-2">
                            {!! Form::label('Type') !!}
                            {!! Form::select('sale_type[]', ['flatsale' => 'Flatsale', 'ota' => 'OTA', 'xta' => 'XTA', 'raffle' => 'Free Raffle', 'pwyw' => 'Pay What You Want'], null, [
                                'class' => 'form-control character-sale-type ',
                                'placeholder' => 'Select Sale Type',
                            ]) !!}
                        </div>


                        <div class="saleType">
                            <div class="mb-3 hide currencyOptions">
                                <h5>Currency Selection</h5>
                                <p>Please only select one type of currency, or it will default to IRL.</p>
                                <p>IRL Currency is any non-site currency such as euros, USD, or CAD.</p>
                                <p>Characters that cannot be sold <strong>cannot</strong> be offered for sale, but they can be raffled or offered in OTA/XTA, provided that you do not specify currency.</p>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        {!! Form::label('Onsite Currency') !!}
                                        {!! Form::select('currency_id[]', $currencies, null, ['class' => 'form-control selectize currency_id', 'placeholder' => 'Select a Currency']) !!}
                                    </div>
                                    <div class="col-md-6 form-group">
                                        {!! Form::label('IRL Currency') !!}
                                        {!! Form::text('irl_currency[]', null, ['class' => 'form-control irl_currency', 'placeholder' => 'Enter a Currency']) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 hide flatOptions">
                                <h5>Flatsale Price</h5>
                                <p>For flatsales, a price is <strong>required.</strong></p>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        {!! Form::label('Price') !!}
                                        {!! Form::number('price[]', null, ['class' => 'form-control price', 'placeholder' => 'Enter a Cost']) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 hide xtaOptions">
                                <div class="form-group">
                                    {!! Form::label('Autobuy (IRL Currency, Optional)') !!}
                                    {!! Form::number('autobuy[]', null, ['class' => 'form-control autobuy', 'placeholder' => 'Enter an Autobuy']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('End Point (Optional)') !!}
                                    {!! Form::text('end_point[]', null, ['class' => 'form-control end_point', 'placeholder' => 'Provide information about when offers close']) !!}
                                </div>
                            </div>

                            <div class="mb-3 hide pwywOptions">
                                <div class="form-group">
                                    {!! Form::label('Minimum Offer') !!}
                                    {!! Form::number('minimum[]', null, ['class' => 'form-control minimum', 'placeholder' => 'Enter a Minimum']) !!}
                                </div>
                            </div>
                            <div class="mb-3 hide raffleOptions">
                                <div class="form-group">
                                    {!! Form::label('End Point') !!}
                                    {!! Form::text('end_point[]', null, ['class' => 'form-control datepicker end_point', 'placeholder' => 'Provide a date for when the raffle closes']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-group my-2">
                            {!! Form::label('Notes (Optional)') !!}
                            {!! Form::text('description[]', null, ['class' => 'form-control description', 'placeholder' => 'Provide any additional notes necessary']) !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
