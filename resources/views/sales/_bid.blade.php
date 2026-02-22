<div class="media-body row mw-100 mx-0" style="flex:1;flex-wrap:wrap;">
    <div class="d-block" style="flex:1">
        <div class="border p-3 rounded {{ $bid->is_featured ? 'border-success bg-light' : '' }} ">
            @if ($bid->isStaffOrGuest)
                <div class="my-1 text-right">
                    <button data-toggle="modal" data-target="#delete-modal-{{ $bid->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1 btn-outline-danger text-uppercase">
                        <i class="fas fa-trash"></i><span class="ml-2 d-none d-sm-inline-block">Delete</span>
                    </button>
                </div>
            @endif
            <small class="text-muted">{!! pretty_date($bid->created_at) !!}</small>
            <h3>
                {!! $bid->user->displayName !!}'s {{ ucfirst($bid->type(true)) }}
                <a href="{{ $bid->url }}"><i class="fas fa-link ml-1" style="opacity: 50%;"></i></a>
                @if ($bid->is_featured)
                    <i class="fas fa-star text-success" data-toggle="tooltip" title="Featured by Owner"></i>
                @endif

                @if ($bid->status == 'Complete')
                    <span class="badge badge-success float-right" data-toggle="tooltip" title="Sale has been completed.">Complete</span>
                @elseif ($bid->status == 'Accepted')
                    <span class="badge badge-info float-right" data-toggle="tooltip" title="Accepted and pending payment request.">Accepted</span>
                @elseif($bid->status == 'Paid')
                    <span class="badge badge-primary float-right" data-toggle="tooltip" title="{{ $bid->user->name }} has marked this {{ $bid->type(true) }} as paid.">Paid</span>
                @elseif($bid->status == 'Requested')
                    <span class="badge badge-warning float-right" data-toggle="tooltip" title="Awaiting payment from {{ $bid->user->name }}">Payment Requested</span>
                @elseif($bid->status == 'Approval')
                    <span class="badge badge-warning float-right" data-toggle="tooltip" title="Character transfer awaiting approval from staff.">In Queue</span>
                @endif
            </h3>
            @if ($bid->salesCharacter->type != 'raffle')
                {!! $bid->price !!}
                @if ($bid->canManageBid && $bid->paymentType == 'irl')
                    | <strong>Invoice Email:</strong> {{ $bid->paypal_email }}
                    @if ($bid->status == 'Accepted')
                        <div class="alert alert-info mt-3">The {{ $bid->type(true) }} has been accepted, but a payment request has not been sent. <small class="text-muted ml-2">{!! pretty_date($bid->updated_at) !!}</small></div>
                    @elseif ($bid->status == 'Requested' && $bid->data['seller_paypal_email'])
                        <div class="alert alert-info mt-3">Payment has been requested from <strong> {{ $bid->data['seller_paypal_email'] }}</strong>. <small class="text-muted ml-2">{!! pretty_date($bid->updated_at) !!}</small></div>
                        <div class="alert alert-warning mt-3">Remember to pay promptly, or you may be subject to disciplinary action. Payment must be handled through paypal, this page only serves as a way to facilitate sales rather than handling
                            them.</div>
                    @elseif ($bid->status == 'Paid')
                        <div class="alert alert-success mt-3">Payment has been sent. Please check before finalizing the sale.<small class="text-muted ml-2">{!! pretty_date($bid->updated_at) !!}</small></div>
                    @endif
                @endif
            @endif
            <h5>Comments</h5>
            {!! isset($bid->data['parsed_comments']) ? $bid->data['parsed_comments'] : '<p>None provided.</p>' !!}
            <div class="my-1 text-right">
                @if ($bid->canManageBid)
                    @if ($bid->status == 'Pending')

                        @if (!$bid->salesCharacter->bids->where('status', 'Accepted')->first())
                            <button data-toggle="modal" data-target="#accept-modal-{{ $bid->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1  btn-success text-uppercase">
                                <i class="fas fa-check"></i><span class="ml-2 d-none d-sm-inline-block">Accept</span>
                            </button>
                        @endif
                        <button data-toggle="modal" data-target="#reject-modal-{{ $bid->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1  btn-danger text-uppercase">
                            <i class="fas fa-minus-circle"></i><span class="ml-2 d-none d-sm-inline-block">Reject</span>
                        </button>
                        <button data-toggle="modal" data-target="#feature-modal-{{ $bid->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1  btn-faded text-success text-uppercase">
                            <i class="fas fa-star"></i>
                            <span class="ml-2 d-none d-sm-inline-block">{{ $bid->is_featured ? 'Unf' : 'F' }}eature {{ ucfirst($bid->type(true)) }}</span>
                        </button>
                    @endif
                    @if ($bid->paymentType == 'irl')
                        @if ($bid->status == 'Accepted')
                            <button data-toggle="modal" data-target="#request-modal-{{ $bid->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1  btn-outline-primary text-uppercase">
                                <i class="fas fa-check"></i><span class="ml-2 d-none d-sm-inline-block">Mark Payment Requested</span>
                            </button>
                        @elseif ($bid->isStaffOrGuest && $bid->status == 'Paid')
                            <button data-toggle="modal" data-target="#complete-modal-{{ $bid->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1  btn-success text-uppercase">
                                <i class="fas fa-check"></i><span class="ml-2 d-none d-sm-inline-block">Finalize Sale</span>
                            </button>
                        @elseif($bid->isBidOwner && $bid->status == 'Requested')
                            <button data-toggle="modal" data-target="#pay-modal-{{ $bid->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1  btn-outline-success text-uppercase">
                                <i class="fas fa-check"></i><span class="ml-2 d-none d-sm-inline-block">Mark Payment Sent</span>
                            </button>
                        @endif
                        @if ($bid->isStaffOrGuest && $bid->status != 'Complete' && $bid->status != 'Pending')
                            <button data-toggle="modal" data-target="#cancel-modal-{{ $bid->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1  btn-warning text-uppercase">
                                <i class="fas fa-minus-circle"></i><span class="ml-2 d-none d-sm-inline-block">Cancel</span>
                            </button>
                        @endif
                    @endif
                @endif
                <?php $commentCount = App\Models\Comment::where('commentable_type', 'App\Models\Sales\SalesBid')->where('commentable_id', $bid->id)->count(); ?>
                @if (!$page)
                    <a class="btn" href="{{ $bid->url }}"><i class="fas fa-comment"></i> {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : '' }}</a>
                @else
                    <span class="btn"><i class="fas fa-comment"></i> {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : '' }}</span>
                @endif
            </div>
        </div>

        @if ($bid->canManageBid)
            <div class="modal fade" id="accept-modal-{{ $bid->getKey() }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Accept {!! $bid->user->displayName !!}'s {{ $bid->type(true) }}</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">This will accept the {{ $bid->type(true) }}.
                                @if ($bid->paymentType == 'irl')
                                    Payment will then have to be confirmed and processed before the character is finally granted to the new owner.
                                @else
                                    Payment will be automatically handled (and refunded if other users deposited currency), and a transfer for the character will begin.
                                @endif
                                Are you sure you want to accept {!! $bid->user->displayName !!}'s {{ $bid->type(true) }}?
                            </div>
                        </div>
                        {!! Form::open(['url' => $bid->url . '/accept']) !!}
                        {!! Form::submit('Accept', ['class' => 'btn btn-primary w-100 mb-0 mx-0']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="modal fade" id="reject-modal-{{ $bid->getKey() }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Reject {{ ucfirst($bid->type(true)) }}</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">This will reject this {{ $bid->type(true) }}, archiving it, but marking it as invalid and allowing the user to make a new {{ $bid->type(true) }}.</div>
                        </div>
                        {!! Form::open(['url' => $bid->url . '/reject']) !!}
                        {!! Form::submit('Reject', ['class' => 'btn btn-danger w-100 mb-0 mx-0']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="modal fade" id="feature-modal-{{ $bid->getKey() }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $bid->is_featured ? 'Unf' : 'F' }}eature {{ ucfirst($bid->type(true)) }}</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">Featuring {{ $bid->type(true) }}s helps to inform other users what your highest valued offers are. Are you sure you want to {{ $bid->is_featured ? 'un' : '' }}feature this
                                {{ $bid->type(true) }}?
                            </div>
                        </div>
                        <div class="alert alert-warning">{{ ucfirst($bid->type(true)) }}s can be unfeatured.</div>
                        {!! Form::open(['url' => $bid->url . '/feature']) !!}
                        @if (!$bid->is_featured)
                            {!! Form::submit('Feature', ['class' => 'btn btn-primary w-100 mb-0 mx-0']) !!}
                        @else
                            {!! Form::submit('Unfeature', ['class' => 'btn btn-primary w-100 mb-0 mx-0']) !!}
                        @endif
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            @if ($bid->paymentType == 'irl')

                @if ($bid->isStaffOrGuest && $bid->status == 'Accepted')
                    <div class="modal fade" id="request-modal-{{ $bid->getKey() }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Mark Payment Requested</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">After you have sent a payment request to {!! $bid->user->displayName !!} 's paypal at <strong><a href="paypal.com">{{ $bid->paypal_email }}</a></strong>, mark this {{ $bid->type(true) }} as
                                        having
                                        been requested. This will
                                        notify {!! $bid->user->displayName !!} to check and send payment.
                                    </div>
                                    <p>Please provide a paypal email so that the buyer knows where to expect to be requested from.</p>

                                    <p class="text-danger">Marking as requested will not automatically send a payment request. All paypal payment functions must be handled manually through the paypal website itself.</p>
                                    {!! Form::open(['url' => $bid->url . '/request']) !!}
                                    <div class="form-group">
                                        {!! Form::label('Paypal Email') !!}
                                        {!! Form::text('email', $bid->recipient->settings->paypal_email ?? null, ['class' => 'form-control']) !!}
                                    </div>

                                    {!! Form::submit('Mark as Requested', ['class' => 'btn btn-primary w-100 mb-0 mx-0']) !!}

                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($bid->isBidOwner && $bid->status == 'Requested')
                    <div class="modal fade" id="pay-modal-{{ $bid->getKey() }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Mark Payment Sent</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">After you have sent payment to {!! $bid->sales->user->displayName !!} 's paypal, mark this {{ $bid->type(true) }} as having been paid. This will
                                        notify {!! $bid->sales->user->displayName !!} to check payment and finalize the sale.
                                    </div>
                                </div>
                                <div class="alert alert-warning">Have you sent payment?</div>
                                {!! Form::open(['url' => $bid->url . '/pay']) !!}
                                {!! Form::submit('Mark as Paid', ['class' => 'btn btn-primary w-100 mb-0 mx-0']) !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                @elseif ($bid->isStaffOrGuest && $bid->status == 'Paid')
                    <div class="modal fade" id="complete-modal-{{ $bid->getKey() }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Complete and Finalize Sale</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">{!! $bid->user->displayName !!} has marked their offer as paid.
                                    </div>
                                    <p>Please ensure that payment has indeed been sent and that all details are correct.</p>

                                </div>
                                <div class="alert alert-danger">Finalization of the sale cannot be undone. Are you sure you want to finalize this sale?</div>
                                {!! Form::open(['url' => $bid->url . '/complete']) !!}
                                {!! Form::submit('Complete Sale', ['class' => 'btn btn-primary w-100 mb-0 mx-0']) !!}

                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                @endif
                @if ($bid->isStaffOrGuest && $bid->status != 'Complete' && $bid->status != 'Pending')
                    <div class="modal fade" id="cancel-modal-{{ $bid->getKey() }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Cancel {{ ucfirst($bid->type(true)) }}</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">This will cancel this {{ $bid->type(true) }}, returning it to Pending and allowing another offer to be selected.</div>

                                </div>
                                {!! Form::open(['url' => $bid->url . '/cancel']) !!}
                                {!! Form::submit('Cancel', ['class' => 'btn btn-warning w-100 mb-0 mx-0']) !!}

                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>
