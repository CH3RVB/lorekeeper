@if($criterion)
<p>{{ $criterion->summary }}{!! $criterion->is_guide_active ? ' - <a href="'.url('/criteria/guide/'.$criterion->id).'">Go To Guide</a>' : '' !!}</p>

@php $finalValues = $values ?? $minRequirements @endphp
<div id="calc-{{ isset($id) ? $id : $criterion->id }}" class="ml-5 steps">
    <h5>
        {{ isset($title) ? $title : 'Minimum Requirements' }}
        <span class="mr-2 text-secondary"> - will reward <span class="reward">{!! $criterion->currency->display($criterion->calculateReward($finalValues)) ?? 0 !!}</span>
    </h5>
    @foreach($criterion->steps->where('is_active', 1) as $step)
    <div class="form-group">
        <div>{!! Form::label($step->name) !!} {!! $step->summary ? add_help($step->summary) : '' !!}</div>
        @if($step->type === 'input')
            {!! Form::number('criterion['.(isset($id) ? $id : $criterion->id).']['.$step->id.']', $finalValues[$step->id] ?? null, ['class' => 'form-control', 'min' => isset($limitByMinReq)  ? $minRequirements[$step->id] ?? null : null]) !!}
        @elseif($step->type === 'options')
            @php 
                $finalOptions = isset($limitByMinReq) && isset($minRequirements) ? $step->options($minRequirements[$step->id])->where('is_active', 1)->pluck('name', 'id') : $step->options->where('is_active', 1)->pluck('name', 'id');
            @endphp
            {!! Form::select('criterion['.(isset($id) ? $id : $criterion->id).']['.$step->id.']', $finalOptions, $finalValues[$step->id] ?? null, ['class' => 'form-control selectize', 'placeholder' => 'Select an Option']) !!}
        @elseif($step->type === 'boolean')
            {!! Form::checkbox('criterion['.(isset($id) ? $id : $criterion->id).']['.$step->id.']', 1, $finalValues[$step->id] ?? 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'disabled' => isset($limitByMinReq) && isset($minRequirements[$step->id]) ? 'disabled' : null]) !!}
        @endif
    </div>
    @endforeach
</div>

<script>
    $(`#calc-{{ isset($id) ? $id : $criterion->id }}.steps`).on('change input', (e) => {
        const calcId = {{ isset($id) ? $id : $criterion->id }};
        const disabledInputs = $('#calc-' + calcId).find('input[disabled]');
        disabledInputs.prop('disabled', false);
        formObj = {};
        let formData = $('#calc-' + calcId).closest('form').serializeArray();
        disabledInputs.prop('disabled', true);
        formObj['_token'] = formData[0].value;
        formData = formData.filter((item) => item.name.includes('criterion[' + calcId + ']'));
        formData.forEach((item) => formObj[item.name.split('[')[2].replace(']', '')] = item.value);
        $(`#calc-${calcId} .reward`).load('{{ url('criteria/rewards/'.$criterion->id) }}', formObj);
    });
</script>
@else
<div>This Criterion no longer exists</div>
@endif