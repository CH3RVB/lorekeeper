<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Criteria\Criterion;
use App\Models\Criteria\CriterionStep;
use App\Models\Criteria\CriterionStepOption;
use App\Models\Currency\Currency;
use App\Services\CriterionService;
use Illuminate\Http\Request;

class CriterionController extends Controller {

  /**
   * Shows the index for creating Criteria
   */
  public function getIndex() {
    return view('admin.criteria.index', [
      'criteria' => Criterion::get()
    ]);
  }


  /**
   * Shows the create / edit page for a criterion
   */
  public function getCreateEditCriterion($id = null) {
    return view('admin.criteria.create_edit_criterion', [
      'criterion' => $id ? Criterion::where('id', $id)->first() : new Criterion,
      'currencies' => Currency::pluck('name', 'id')->toArray()
    ]);
  }

  /**
   * Creates a Criterion
   */
  public function postCreateEditCriterion(Request $request, CriterionService $service, $id = null) {
    $id ? $request->validate(Criterion::$updateRules) : $request->validate(Criterion::$createRules);
    $data = $request->only(['name', 'currency_id', 'is_active', 'summary', 'is_guide_active', 'base_value', 'sort', 'rounding', 'round_precision']);

    if ($id && $service->updateCriterion(Criterion::find($id), $data)) {
      flash('Criterion updated successfully.')->success();
    } else if (!$id && $criterion = $service->createCriterion($data)) {
      flash('Criterion created successfully.')->success();
      return redirect()->to('admin/data/criteria/edit/' . $criterion->id);
    } else {
      foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
    }
    return redirect()->back();
  }

  /**
   * Gets the criterion deletion modal.
   *
   * @param  int  $id
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function getDeleteCriterion($id) {
    $criterion = Criterion::find($id);
    return view('admin.criteria._delete_criterion', [
      'criterion' => $criterion,
      'name' => 'Criterion'
    ]);
  }

  /**
   * Deletes an criterion.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  App\Services\ItemService  $service
   * @param  int                       $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function postDeleteCriterion(Request $request, CriterionService $service, $id) {
    if ($id && $service->deleteCriterion(Criterion::find($id))) {
      flash('Criterion deleted successfully.')->success();
    } else {
      foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
    }
    return redirect()->to('admin/data/criteria');
  }

  /**
   * Shows the create / edit page for a criterion step
   */
  public function getCreateEditCriterionStep($id, $step_id = null) {

    return view('admin.criteria.create_edit_criterion_step', [
      'criterionId' => $id,
      'step' => $step_id ? CriterionStep::where('id', $step_id)->first() : new CriterionStep,
    ]);
  }

  /**
   * Creates a Criterion Step
   */
  public function postCreateEditCriterionStep(Request $request, CriterionService $service, $id, $step_id = null) {
    $step_id ? $request->validate(CriterionStep::$updateRules) : $request->validate(CriterionStep::$createRules);
    $data = $request->only(['name', 'summary', 'image', 'remove_image', 'description', 'parsed_description', 'is_active', 'type', 'calc_type', 'input_calc_type', 'options', 'sort']);
    $data['criterion_id'] = $id;

    if ($step_id && $service->updateCriterionStep(CriterionStep::find($step_id), $data)) {
      flash('Criterion Step updated successfully.')->success();
    } else if (!$step_id && $step = $service->createCriterionStep($data)) {
      flash('Criterion Step created successfully.')->success();
      return redirect()->to('admin/data/criteria/' . $id . '/step/' . $step->id);
    } else {
      foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
    }
    return redirect()->back();
  }


  /**
   * Gets the criterion deletion modal.
   *
   * @param  int  $id
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function getDeleteCriterionStep($id) {
    $step = CriterionStep::find($id);
    return view('admin.criteria._delete_criterion', [
      'criterion' => $step,
      'name' => 'Step',
      'path' => 'step/'
    ]);
  }

  /**
   * Deletes an criterion.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  App\Services\ItemService  $service
   * @param  int                       $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function postDeleteCriterionStep(Request $request, CriterionService $service, $id) {
    $criterion_id = CriterionStep::find($id)->criterion_id;
    if ($id && $service->deleteCriterionStep(CriterionStep::find($id))) {
      flash('Criterion step deleted successfully.')->success();
    } else {
      foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
    }
    return redirect()->to('admin/data/criteria/edit/' . $criterion_id);
  }


  /**
   * Shows the create / edit page for a criterion step
   */
  public function getCreateEditCriterionOption($stepId, $id = null) {
    return view('admin.criteria._create_edit_option', [
      'stepId' => $stepId,
      'option' => $id ? CriterionStepOption::where('id', $id)->first() : new CriterionStepOption,
    ]);
  }

  /**
   * Creates a Criterion Step
   */
  public function postCreateEditCriterionOption(Request $request, CriterionService $service, $step_id, $id = null) {
    $step_id ? $request->validate(CriterionStepOption::$updateRules) : $request->validate(CriterionStepOption::$createRules);
    $data = $request->only(['name', 'summary', 'description', 'parsed_description', 'is_active', 'amount']);
    $data['criterion_step_id'] = $step_id;

    if ($id && $service->updateCriterionOption(CriterionStepOption::find($id), $data)) {
      flash('Criterion Option updated successfully.')->success();
    } else if (!$id && $option = $service->createCriterionOption($data)) {
      flash('Criterion Option created successfully.')->success();
      return redirect()->to('admin/data/criteria/' . $option->step->criterion_id . '/step/' . $step_id);
    } else {
      foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
    }
    return redirect()->back();
  }


  /**
   * Gets the criterion deletion modal.
   *
   * @param  int  $id
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function getDeleteCriterionOption($id) {
    $option = CriterionStepOption::find($id);
    return view('admin.criteria._delete_criterion', [
      'criterion' => $option,
      'name' => 'Option',
      'path' => 'option/'
    ]);
  }

  /**
   * Deletes an criterion option
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  App\Services\ItemService  $service
   * @param  int                       $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function postDeleteCriterionOption(Request $request, CriterionService $service, $id) {
    $step = CriterionStepOption::find($id)->step;
    if ($id && $service->deleteCriterionOption(CriterionStepOption::find($id))) {
      flash('Criterion step deleted successfully.')->success();
    } else {
      foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
    }
    return redirect()->to('admin/data/criteria/' . $step->criterion_id . '/step/' . $step->id);
  }
}
