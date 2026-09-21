@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')

    <div class="row">

        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>120</h3>
                    <p>Pasien Rawat Inap</p>
                </div>

                <div class="icon">
                    <i class="fas fa-bed"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>85</h3>
                    <p>Pasien Rawat Jalan</p>
                </div>

                <div class="icon">
                    <i class="fas fa-user-md"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>25</h3>
                    <p>IGD</p>
                </div>

                <div class="icon">
                    <i class="fas fa-ambulance"></i>
                </div>
            </div>
        </div>

    </div>


    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                Selamat Datang
            </h3>
        </div>

        <div class="card-body">

            <h4>AdminLTE Laravel berhasil tampil.</h4>

            <p>
                Halaman ini sementara digunakan untuk memastikan
                layout AdminLTE sudah berjalan dengan baik.
            </p>

        </div>

    </div>

@stop
