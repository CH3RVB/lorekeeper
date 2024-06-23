@extends('home.layout')

@section('home-title') New Submission @endsection

@section('home-content')
@if($isClaim)
    {!! breadcrumbs(['Claims' => 'claims', 'New Claim' => 'claims/new']) !!}
@else
    {!! breadcrumbs(['Prompt Submissions' => 'submissions', 'New Submission' => 'submissions/new']) !!}
@endif

<h1>
    @if($isClaim)
        New Claim
    @else
        New Submission
    @endif
</h1>

@if($closed)
    <div class="alert alert-danger">
        The {{ $isClaim ? 'claim' : 'submission' }} queue is currently closed. You cannot make a new {{ $isClaim ? 'claim' : 'submission' }} at this time.
    </div>
@else
    {!! Form::open(['url' => $isClaim ? 'claims/new' : 'submissions/new', 'id' => 'submissionForm',  'onsubmit' => "$(this).find('input').prop('disabled', false)"]) !!}
        @if(!$isClaim)
            <div class="form-group">
                {!! Form::label('prompt_id', 'Prompt') !!}
                {!! Form::select('prompt_id', $prompts, Request::get('prompt_id'), ['class' => 'form-control selectize', 'id' => 'prompt', 'placeholder' => '']) !!}
            </div>
        @endif
        <div class="form-group">
            {!! Form::label('url', $isClaim ? 'URL (Optional)' : 'Submission URL (Optional)') !!}
            @if($isClaim)
                {!! add_help('Enter a URL relevant to your claim (for example, a comment proving you may make this claim).') !!}
            @else
                {!! add_help('Enter the URL of your submission (whether uploaded to dA or some other hosting service).') !!}
            @endif
            {!! Form::text('url', null, ['class' => 'form-control', 'required']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('Enter a comment for your ' . ($isClaim ? 'claim' : 'submission') . ' (no HTML). This will be viewed by the mods when reviewing your ' . ($isClaim ? 'claim' : 'submission') . '.') !!}
            {!! Form::textarea('comments', null, ['class' => 'form-control']) !!}
        </div>

        @if(!$isClaim)
        <div id="criterion-section" class="{{ Request::get('prompt_id') ? '' : 'hide' }}">
            <h2 class="mt-5">Criteria Rewards <button class="btn  btn-outline-info float-right add-calc" type="button">Add Criterion</a></h2>
            <p>Criteria can be used in addition to or in replacement of rewards. They take input on what you are turning in for the prompt in order to calculate your final reward.</p>
            <p>Criteria may populate in with pre-selected minimum requirements for this prompt. </p>
            <div id="criteria"></div>
            <div class="mb-4"></div>
        </div>
        @endif

        <h2>Rewards</h2>
        @if($isClaim)
            <p>Select the rewards you would like to claim.</p>
        @else
            <p>Note that any rewards added here are <u>in addition</u> to the default prompt rewards and caculated rewards. If you do not require any additional rewards, you can leave this blank.</p>
        @endif
        @if($isClaim)
            @include('widgets._loot_select', ['loots' => null, 'showLootTables' => false, 'showRaffles' => true])
        @else
            @include('widgets._loot_select', ['loots' => null, 'showLootTables' => false, 'showRaffles' => false])
        @endif
        @if(!$isClaim)
            <div id="rewards" class="mb-3"></div>
        @endif

        <h2>Characters</h2>
        @if($isClaim)
            <p>If there are character-specific rewards you would like to claim, attach them here. Otherwise, this section can be left blank.</p>
        @endif
        <div id="characters" class="mb-3">
        </div>
        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="addCharacter">Add Character</a>
        </div>

        <h2>Add-Ons</h2>
        <p>If your {{ $isClaim ? 'claim' : 'submission' }} consumes items, attach them here. Otherwise, this section can be left blank. These items will be removed from your inventory but refunded if your {{ $isClaim ? 'claim' : 'submission' }} is rejected.</p>
        <div id="addons" class="mb-3">
        @include('widgets._inventory_select', ['user' => Auth::user(), 'inventory' => $inventory, 'categories' => $categories, 'selected' => [], 'page' => $page])
        @include('widgets._bank_select', ['owner' => Auth::user(), 'selected' => null])
        </div>

        <div class="text-right">
            <a href="#" class="btn btn-primary" id="submitButton">Submit</a>
        </div>
    {!! Form::close() !!}
    
    
    <div id="copy-calc" class="card p-3 mb-2 pl-0 hide">
    @if(isset($criteria))
        @include('criteria._criterion_selector', ['criteria' => $criteria])
    @endif
    </div>

    @include('widgets._character_select', ['characterCurrencies' => $characterCurrencies, 'showLootTables' => false])
    @if($isClaim)
        @include('widgets._loot_select_row', ['items' => $items, 'currencies' => $currencies, 'showLootTables' => false, 'showRaffles' => true])
    @else
        @include('widgets._loot_select_row', ['items' => $items, 'currencies' => $currencies, 'showLootTables' => false, 'showRaffles' => false])
    @endif

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm  {{ $isClaim ? 'Claim' : 'Submission' }}</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will submit the form and put it into the {{ $isClaim ? 'claims' : 'prompt' }} approval queue. You will not be able to edit the contents after the {{ $isClaim ? 'claim' : 'submission' }} has been made. Click the Confirm button to complete the  {{ $isClaim ? 'claim' : 'submission' }}.</p>
                    <div class="text-right">
                        <a href="#" id="formSubmit" class="btn btn-primary">Confirm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
@parent
@if(!$closed)
    @if($isClaim)
        @include('js._loot_js', ['showLootTables' => false, 'showRaffles' => true])
    @else
        @include('js._loot_js', ['showLootTables' => false, 'showRaffles' => false])
    @endif
    @include('js._character_select_js')
    @include('widgets._inventory_select_js', ['readOnly' => true])
    @include('widgets._bank_select_row', ['owners' => [Auth::user()]])
    @include('widgets._bank_select_js', [])

    <script>
        $(document).ready(function() {
            var $submitButton = $('#submitButton');
            var $confirmationModal = $('#confirmationModal');
            var $formSubmit = $('#formSubmit');
            var $submissionForm = $('#submissionForm');

            @if(!$isClaim)
                var $prompt = $('#prompt');
                var $rewards = $('#rewards');

                $prompt.selectize();
                $prompt.on('change', function(e) {
                    $rewards.load('{{ url('submissions/new/prompt') }}/'+$(this).val());
                    console.log('{{ url('criteria/prompt') }}/' + $(this).val());
                    $('#copy-calc').load('{{ url('criteria/prompt') }}/' + $(this).val());
                    if($(this).val()) $('#criterion-section').removeClass('hide');
                });
            @endif

            $submitButton.on('click', function(e) {
                e.preventDefault();
                $confirmationModal.modal('show');
            });

            $formSubmit.on('click', function(e) {
                e.preventDefault();
                $submissionForm.submit();
            });
            
            $('.add-calc').on('click', function(e) {
                e.preventDefault();
                var clone = $('#copy-calc').clone();
                clone.removeClass('hide');
                var input = clone.find('[name*=criterion]');
                var count = $('.criterion-select').length;
                input.attr('name', input.attr('name').replace('#', count))
                clone.find('.criterion-select').on('change', loadForm);
                clone.find('.delete-calc').on('click', deleteCriterion);
                clone.removeAttr('id');
                $('#criteria').append(clone);
            });
            
            $('.delete-calc').on('click', deleteCriterion);
            
            function deleteCriterion (e) {
                e.preventDefault();
                var toDelete = $(this).closest('.card');
                toDelete.remove();
            }
            
            function loadForm (e) {
                var id = $(this).val();
                var promptId = $prompt.val();
                var formId = $(this).attr('name').split('[')[1].replace(']', '');
                
                if(id) {
                    var form = $(this).closest('.card').find('.form');
                    form.load("{{ url('criteria/prompt') }}/" + id + "/" + promptId + "/" + formId,( response, status, xhr ) => {
                        if ( status == "error" ) {
                            var msg = "Error: ";
                            console.error( msg + xhr.status + " " + xhr.statusText );
                        } else {
                            form.find('[data-toggle=tooltip]').tooltip({html: true});
                            form.find('[data-toggle=toggle]').bootstrapToggle();
                        }
                    });
                }
            }
            
            $('.criterion-select').on('change', loadForm)
        });
    </script>
@endif
@endsection
