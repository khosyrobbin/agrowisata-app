<?php

namespace App\Http\Controllers;

use App\Models\TransaksiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        $mydate = Carbon::now();

        $transaksi = TransaksiModel::select(DB::raw("COUNT(*) as count"), DB::raw("MONTHNAME(tanggal_transaksi) as month_name"))
            ->where('status','=','settlement')
            ->whereYear('tanggal_transaksi', date('Y'))
            ->groupBy(DB::raw("month_name"))
            ->orderBy('id_transaksi', 'ASC')
            ->pluck('count', 'month_name');

        $labels = $transaksi->keys();
        $data = $transaksi->values();

        return view('layout-admin.dashboard', compact('labels', 'data'));
    }
    public function listUser()
    {
        $user = User::all();
        return view('layout-admin.listUser', compact('user'));
    }

    public function pendapatan(Request $request)
    {
        $data['start'] = $request->query('start');
        $data['end'] = $request->query('end');

        $query = TransaksiModel::select('transaksi.*', 'users.*', 'meja.*')
            ->join('users', 'users.id', '=', 'transaksi.id')
            ->join('meja', 'meja.id_meja', '=', 'transaksi.id_meja');

        if ($data['start'])
            $query->whereDate('tanggal_transaksi', '>=', $data['start']);
        if ($data['end'])
            $query->whereDate('tanggal_transaksi', '<=', $data['end']);

        $data['transaksi'] = $query->orderby('tanggal_transaksi', 'desc')->where('status', '=', 'settlement')->paginate(15)->withQueryString();
        $pendapatan = DB::table('transaksi')->sum('tagihan');
        // $transaksi = [
        //     'transaksi' => $this->TransaksiModel->allData(),
        // ];
        return view('layout-admin.pendapatan', $data);
    }
}
