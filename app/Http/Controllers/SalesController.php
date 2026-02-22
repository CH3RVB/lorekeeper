<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesBid;
use App\Models\Sales\SalesCharacter;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class SalesController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | sales Controller
    |--------------------------------------------------------------------------
    |
    | Displays sales posts and updates the user's sales read status.
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        View::share('forsale', Sales::admin()->visible()->orderBy('updated_at', 'DESC')->where('is_open', 1)->get());
        View::share('recentsales', Sales::admin()->visible()->orderBy('updated_at', 'DESC')->take(10)->get());
    }

    /**
     * Shows the sales index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        if (Auth::check() && Auth::user()->is_sales_unread) {
            Auth::user()->update(['is_sales_unread' => 0]);
        }

        $query = Sales::visible()->admin();
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

        return view('sales.index', [
            'saleses' => $query->paginate(10)->appends($request->query()),
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
        $sales = Sales::admin()->where('id', $id)->visible(Auth::user() ?? null)->first();
        if (! $sales) {
            abort(404);
        }

        return view('sales.sales', [
            'sales' => $sales,
            'bids'  => $sales->bids()->orderBy('created_at', 'DESC')->paginate(20),
        ]);
    }

    /**********************************************************************************************

        BUY

    **********************************************************************************************/

    /**
     * Get the sales bid modal
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMakeBid($id)
    {
        $character = SalesCharacter::find($id);
        if (! $character || ! $character->canBid()) {
            abort(404);
        }

        return view('sales._make_bid', [
            'character' => $character,
            'sales'     => $character->sales,
            'data'      => $character->data,
        ]);
    }

    /**
     * Make a sales bid
     *
     * @param App\Services\SalesService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postMakeBid(Request $request, SalesService $service, $id)
    {
        $character = SalesCharacter::find($id);
        if (! $character || ! $character->canBid()) {
            abort(404);
        }
        $data = $request->only([
            'price', 'autobuy', 'confirm_ab', 'email','comments'
        ]);
        if ($bid = $service->makeBid($character, Auth::user(), $data)) {
            flash(ucfirst($bid->type(true)) . ' made successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * View an individual bid
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getViewBid($id)
    {
        $bid = SalesBid::find($id);
        if (! $bid || ! $bid->canViewBid) {
            abort(404);
        }
        $character = $bid->salesCharacter;
        if (! $character) {
            abort(404);
        }
        //dont need to check canviewbid again since they check the same info

        return view('sales.bid', [
            'bid'       => $bid,
            'character' => $character,
            'sales'     => $character->sales,
            'bidder'    => $bid->user,
            'page'      => true,
            'admin'     => $character->sales->is_admin,
        ]);
    }

    /**
     * Manage a bid
     *
     * @param App\Services\SalesService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postManageBid(Request $request, SalesService $service, $id, $action)
    {
        $bid = SalesBid::find($id);
        if (! $bid || ! $bid->canManageBid) {
            abort(404);
        }

        //i hope no one is actually looking at all this
        if ($action == 'accept' && $service->acceptBid($bid, Auth::user())) {
            flash(ucfirst($bid->type(true)) . ' accepted successfully.')->success();
        } elseif ($action == 'reject' && $service->rejectBid($bid, Auth::user())) {
            flash(ucfirst($bid->type(true)) . ' rejected successfully.')->success();

        } elseif ($action == 'feature' && $service->featureBid($bid, Auth::user())) {
            flash(ucfirst($bid->type(true)) . ' featured successfully.')->success();
        }elseif ($action == 'request' && $service->requestBid($bid, Auth::user(), $request->only(['email']))) {
            flash(ucfirst($bid->type(true)) . ' payment requested successfully.')->success();
        }elseif ($action == 'complete' && $service->completeBid($bid, Auth::user())) {
            flash(ucfirst($bid->type(true)) . ' finalized successfully.')->success();
        } elseif ($action == 'pay' && $service->payBid($bid, Auth::user())) {
            //buyer marks their payment
            flash(ucfirst($bid->type(true)) . ' marked as paid successfully.')->success();
        }elseif ($action == 'cancel' && $service->cancelBid($bid, Auth::user())) {
            flash(ucfirst($bid->type(true)) . ' cancelled successfully.')->success();
        }else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }
        return redirect()->back();
    }

    /**
     * View all a character's bids
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getViewAllBids($id)
    {
        $character = SalesCharacter::find($id);
        if (! $character || ! $character->canViewBid) {
            abort(404);
        }

        return view('sales.character', [
            'bids'      => $character->bids()->orderBy('is_featured', 'DESC')->orderBy('created_at', 'DESC')->get()->paginate(10),
            'character' => $character,
            'sales'     => $character->sales,
            'page'      => true,
            'admin'     => $character->sales->is_admin,
        ]);
    }

    /**
     * Get the roll modal for an individual character
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRollCharacter($id)
    {
        $character = SalesCharacter::find($id);
        if (! $character || !Auth::check() || !Auth::user()->hasPower('manage_sales')) {
            abort(404);
        }

        return view('sales._roll_character', [
            'character' => $character,
        ]);
    }

    /**
     * Roll an individual character's bids
     *
     * @param App\Services\SalesService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRollCharacter(SalesService $service, $id)
    {
        $character = SalesCharacter::find($id);
         if (! $character || !Auth::check() || !Auth::user()->hasPower('manage_sales')) {
            abort(404);
        }
        if ($service->rollCharacter($character, Auth::user())) {
            flash('Character rolled successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

     /**
     * Get the roll entry modal
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRollSales($id)
    {
        $sales = Sales::find($id);
         if (! $sales || !Auth::check() || !Auth::user()->hasPower('manage_sales')) {
            abort(404);
        }

        return view('sales.roll_sales', [
            'sales' => $sales,
        ]);
    }

    /**
     * Roll raffle entries.
     *
     * @param App\Services\SalesService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRollSales(SalesService $service, $id)
    {
        $sales = Sales::find($id);
         if (! $sales || !Auth::check() || !Auth::user()->hasPower('manage_sales')) {
            abort(404);
        }
        if ($service->rollSales($sales, Auth::user())) {
            flash('Sales rolled successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
