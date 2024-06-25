<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Criteria\Criterion;
use App\Models\Gallery\GalleryCriterion;
use App\Models\Prompt\PromptCriterion;
use Illuminate\Http\Request;
use App\Models\Currency\Currency;

class CriterionController extends Controller
{
    
     /**
     * returns a criterion's guide page
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCriterionGuide($id) {
        $criterion = Criterion::where('id', $id)->first();
        
        if(!$criterion->is_guide_active) abort(404);
        
        return view('criteria.guide',[
            'criterion' => $criterion,
        ]);
    }
    
    /**
     * returns a criterion's form based on steps
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCriterionFormLimited($id) {
        
        return view('criteria._minimum_requirements',[
            'criterion' => Criterion::where('id', $id)->first(),
            'minRequirements' => null,
        ]);
    }
    
     /**
     * returns a criterion's form based on steps
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
     public function getCriterionForm($entity = null, $id, $entity_id = null, $form_id = null) {
        if($entity_id && $entity) {
            if($entity === 'prompt') {
                $entityCriteria = PromptCriterion::where('prompt_id', $entity_id)->where('criterion_id', $id)->first();
            } else if ($entity === 'gallery') {
                $entityCriteria = GalleryCriterion::where('gallery_id', $entity_id)->where('criterion_id', $id)->first();
            }
        }
        
        return view('criteria._minimum_requirements',[
            'criterion' => Criterion::where('id', $id)->first(),
            'minRequirements' => isset($entityCriteria) ? $entityCriteria->minRequirements : null,
            'title' => isset($entityCriteria) ? 'Criterion Options' : null,
            'limitByMinReq' => isset($entityCriteria) ? true : null,
            'id' => $form_id,
            'criterion_currency' => isset($entityCriteria->criterion_currency_id) ? $entityCriteria->criterion_currency_id : $entityCriteria->criterion->currency_id,
        ]);
    }
    
     /**
     * returns a criterion dd based on entity
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCriterionSelector($entity, $id) {
        if($entity === 'prompt') {
            $entityCriteria= PromptCriterion::where('prompt_id', $id)->pluck('criterion_id')->toArray();
        } else if ($entity === 'gallery') {
            $entityCriteria = GalleryCriterion::where('gallery_id', $id)->pluck('criterion_id')->toArray();
        }
        
        
        $criteria = Criterion::whereIn('id', $entityCriteria)->pluck('name', 'id');
        return view('criteria._criterion_selector',[
            'criteria' => $criteria,
        ]);
    }
    
     /**
     * returns an amount based on a criterion id and data
     *
     * @param  \Illuminate\Http\Request    $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postCriterionRewards($id, Request $request) {
        $stepData = $request->except('_token');
        $criterion = Criterion::where('id', $id)->first();

        if(isset($stepData['criterion_currency']) && $stepData['criterion_currency']){
            $currencyval = Currency::find($stepData['criterion_currency'])->display($criterion->calculateReward($stepData));
        }else{
            $currencyval = $criterion->currency->display($criterion->calculateReward($stepData));
        }
        
        return $currencyval;
    }
}
