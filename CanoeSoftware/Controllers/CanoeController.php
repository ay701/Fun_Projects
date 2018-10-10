<?php
namespace Emp\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Redis;

use Emp\Models;
use Emp\Models\CanoeClients;
use Emp\Models\CanoeFunds;
use Emp\Models\CanoeInvestments;
use Emp\Models\CanoeCashFlows;

use Input;
use DB;
use Auth;
use Exception;
use Artisan;

class CanoeController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    /**
     *	Get the Contact Search Page
     *
     */
    public function index ()
    {
//        $this->add_clients();
//        $this->add_funds();
//        $this->add_investments();

        $data = array();
        $clients = CanoeClients::all();

        return view("canoe.index", array("clients" => $clients));
    }

    public function getClient ($id)
    {
        $data = array();

        $client = CanoeClients::find($id);
        $client->permissions = explode(",", $client->permission);

        if(isset($client->id))
            $funds = CanoeFunds::all();

        $data["client"] = $client;
        $data["funds"] = $funds;

        return view("canoe.client", $data);
    }

    public function cashflows()
    {
        $data = array();
        $data["clients"] = CanoeClients::all();

        return view("canoe.cashflows", $data);
    }

    public function fundsByType($type)
    {
        $funds = CanoeFunds::select("id", "name")
            ->where("type", $type)
            ->get();

        return response()->json( array('success' => true, 'investment_names' => $funds) );
    }

    public function getInvestments($client_id, $fund_id)
    {
        $investments = CanoeInvestments::select("amount")
            ->where("client_id", $client_id)
            ->where("fund_id", $fund_id)
            ->first();
//
//        $amount = 0;
//
//        foreach($investments as $investment){
//            CanoeCashFlows::select(return)
//            ->where("investment_id", $investment->id)
//                ->first();
//        }

        return response()->json( array('success' => true, 'investments' => $investments) );

    }

    public function calculateInvestment()
    {
        $current_value = Input::get("current_value");
        $calc_value = Input::get("calc_value");
        $updated_value = $current_value*(1+$calc_value);

        return response()->json( array('success' => true, 'updated_value' => $updated_value) );
    }

    public function addCashFlow()
    {
        $cash_flow = new CanoeCashFlows;
        $cash_flow->investment_id = Input::get("investment_id");
        $cash_flow->date = Input::get("date");
        $cash_flow->return = Input::get("return");
        $cash_flow->save();

        return response()->json( array('success' => true, 'message' => 'Cash flow added successfully!') );
    }

    private function add_clients()
    {
        $client = new CanoeClients();
        $client->name = "Client 1";
        $client->permission = "HF,PL,VC,RE,PC";
        $client->description = "Client 1 description";
        $client->preference = "";
        $client->save();

        $client = new CanoeClients();
        $client->name = "Client 2";
        $client->permission = "VC,RE";
        $client->description = "Client 2 description";
        $client->preference = "";
        $client->save();

        $client = new CanoeClients();
        $client->name = "Client 3";
        $client->permission = "HF,PL,VC,RE,PC";
        $client->description = "Client 3 description";
        $client->preference = "";
        $client->save();
    }

    private function add_funds()
    {
        $fund = new CanoeFunds();
        $fund->name = "ABC";
        $fund->type = "HF";
        $fund->inception_date = "2018-01-20";
        $fund->description = "description ABC";
        $fund->save();

        $fund = new CanoeFunds();
        $fund->name = "DEF";
        $fund->type = "VC";
        $fund->inception_date = "2018-02-01";
        $fund->description = "description DEF";
        $fund->save();

        $fund = new CanoeFunds();
        $fund->name = "XYZ";
        $fund->type = "RE";
        $fund->inception_date = "2018-01-01";
        $fund->description = "description XYZ";
        $fund->save();

        $fund = new CanoeFunds();
        $fund->name = "GHI";
        $fund->type = "PC";
        $fund->inception_date = "2018-04-01";
        $fund->description = "description GHI";
        $fund->save();

        $fund = new CanoeFunds();
        $fund->name = "JKL";
        $fund->type = "PL";
        $fund->inception_date = "2018-05-01";
        $fund->description = "description JKL";
        $fund->save();
    }

    private function add_investments()
    {
        $investment = new CanoeInvestments();
        $investment->client_id = 2;
        $investment->fund_id = 16;
        $investment->date = "2018-05-01";
        $investment->amount = 9500.50;
        $investment->save();

        $investment = new CanoeInvestments();
        $investment->client_id = 2;
        $investment->fund_id = 17;
        $investment->date = "2018-05-01";
        $investment->amount = 6800.50;
        $investment->save();
    }

}
