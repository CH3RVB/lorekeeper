<?php
namespace App\Http\Controllers;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Queue\Queue;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesBid;
use App\Models\Sales\SalesCharacter;
use App\Services\UserSalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class UserSalesController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Sales Controller
    |--------------------------------------------------------------------------
    |
    | Displays/manages sales posts and updates the user's sales read status.
    |
    */

    /**********************************************************************************************

        BROWSE

    **********************************************************************************************/

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        View::share('forsale', Sales::visible()->users()->open()->orderBy('updated_at', 'DESC')->get());
        View::share('recentsales', Sales::visible()->users()->open()->orderBy('updated_at', 'DESC')->take(10)->get());

        if(!Settings::get('allow_user_sales')) abort(404);
    }

    /**
     * Shows the sales index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        if (Auth::check() && Auth::user()->is_user_sales_unread) {
            Auth::user()->update(['is_user_sales_unread' => 0]);
        }

        $query = Sales::visible()->users();
        $data  = $request->only(['title', 'is_open', 'sort']);
        if (isset($data['is_open']) && $data['is_open'] != 'none') {
            $query->where('is_open', $data['is_open']);
        }
        if (isset($data['title'])) {
            $query->where('title', 'LIKE', '%' . $data['title'] . '%');
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
                case 'bump':
                    $query->sortBump();
                    break;
                case 'bump-reverse':
                    $query->sortBump(true);
                    break;
            }
        } else {
            $query->sortBump(true);
        }

        return view('user_sales.index', [
            'saleses' => $query->paginate(10)->appends($request->query()),
            'queue'   => Queue::find(Settings::get('sales_queue_id')),
        ]);
    }

    /**
     * Shows a sales post.
     *
     * @param int         $id
     * @param string|null $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSales($id, $slug = null)
    {
        $sales = Sales::where('id', $id)->users()->visible(Auth::user() ?? null)->first();
        if (! $sales) {
            abort(404);
        }

        return view('user_sales.sales', [
            'sales' => $sales,
            'bids'  => $sales->bids()->orderBy('created_at', 'DESC')->paginate(20),
        ]);
    }


    /**********************************************************************************************

        MANAGE

    **********************************************************************************************/

    /**
     * Shows the Sales index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMySales()
    {
        return view('user_sales.my_sales', [
            'saleses' => Auth::user()->sales()->orderBy('title', 'DESC')->paginate(20),
            'queue'   => Queue::find(Settings::get('sales_queue_id')),
        ]);
    }

    /**
     * Shows the edit Sales page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSales($id)
    {
        $sales = Sales::users()->find($id);
        if (! $sales) {
            abort(404);
        }

        return view('user_sales.create_edit_sales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Creates or edits a Sales page.
     *
     * @param App\Services\UserSalesService $service
     * @param int|null                  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditSales(Request $request, UserSalesService $service, $id = null)
    {
        $id ? $request->validate(Sales::$updateRules) : $request->validate(Sales::$createRules);
        $data = $request->all();
        if ($id && $service->updateSales(Sales::users()->find($id), $data, Auth::user())) {
            flash('Sales updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the Sales deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteSales($id)
    {
        $sales = Sales::find($id);

        return view('user_sales._delete_sales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Deletes a Sales page.
     *
     * @param App\Services\UserSalesService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSales(Request $request, UserSalesService $service, $id)
    {
        if ($id && $service->deleteSales(Sales::find($id))) {
            flash('Sales deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('user-sales');
    }

}
