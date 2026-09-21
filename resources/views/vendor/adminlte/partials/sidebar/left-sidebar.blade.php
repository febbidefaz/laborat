<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    @if (config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    <div class="sidebar">

        @if (session('userlab_id'))
            <div class="user-panel-lab">

                <div class="user-icon-lab">
                    <i class="fas fa-flask"></i>
                </div>


                <div class="user-info-lab">

                    <div class="user-name-lab">
                        {{ session('userlab_nama') }}
                    </div>


                    <div class="user-role-lab">
                        {{ session('userlab_role') }}
                    </div>

                </div>

            </div>
        @endif



        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if (config('adminlte.sidebar_nav_animation_speed') != 300) data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}" @endif
                @if (!config('adminlte.sidebar_nav_accordion')) data-accordion="false" @endif>

                @foreach ($adminlte->menu('sidebar') as $item)
                    @include('adminlte::partials.sidebar.menu-item', ['item' => $item])

                    @if (($item['text'] ?? '') === 'Rawat Jalan')
                        <li class="nav-item">

                            <a href="#modalCariPasien" class="nav-link" data-toggle="modal"
                                data-target="#modalCariPasien">

                                <i class="nav-icon fas fa-search"></i>
                                <p>Cari ID</p>

                            </a>

                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>
</aside>



{{-- ========================================= --}}
{{-- MODAL CARI PASIEN --}}
{{-- ========================================= --}}
<div class="modal fade" id="modalCariPasien" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h4 class="modal-title">
                    Cari Pasien
                </h4>

                <button type="button" class="close" data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <div class="modal-body">

                <input type="text" id="cariPasien" class="form-control" placeholder="Ketikkan ID pasien"
                    autocomplete="off">

                <div id="hasilCariPasien" class="mt-3">
                </div>

            </div>


            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">

                    Batal

                </button>


                <button type="button" class="btn btn-primary" onclick="cariPasien()">

                    Cari

                </button>

            </div>

        </div>

    </div>

</div>


<style>
    .user-panel-lab {

        margin: 18px 10px 15px 10px;

        padding: 12px;

        display: flex;

        align-items: center;

        gap: 12px;

        background: linear-gradient(135deg,
                #3457c0,
                #3f66d6);

        border-radius: 10px;

        box-shadow:
            0 4px 12px rgba(0, 0, 0, .25);

    }


    .user-icon-lab {

        width: 45px;

        height: 45px;

        border-radius: 50%;

        display: flex;

        align-items: center;

        justify-content: center;

        background:
            rgba(255, 255, 255, .20);

        color: white;

        font-size: 22px;

    }


    .user-info-lab {

        color: white;

        line-height: 1.2;

    }


    .user-name-lab {

        font-size: 16px;

        font-weight: 700;

    }


    .user-role-lab {

        margin-top: 4px;

        font-size: 13px;

        opacity: .85;

    }

    .user-panel-rm {
        margin: 18px 10px 22px 10px;
        padding: 16px 14px;
        display: flex;
        align-items: center;
        gap: 14px;
        background: linear-gradient(135deg, #3457c0, #3f66d6);
        border-radius: 10px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.25);
    }

    .user-icon-rm {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 24px;
        background: rgba(255, 255, 255, 0.15);
    }

    .user-info-rm {
        color: #ffffff;
        line-height: 1.2;
    }

    .user-name-rm {
        font-size: 18px;
        font-weight: 600;
    }

    .user-role-rm {
        margin-top: 4px;
        font-size: 14px;
        opacity: 0.85;
    }

    .nav-sidebar .nav-item>.nav-link.active {
        background-color: #3F66D6 !important;
        color: #ffffff !important;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(15, 118, 110, 0.35);
    }

    .nav-sidebar .nav-item>.nav-link.active i {
        color: #ffffff !important;
    }

    .nav-sidebar .nav-item>.nav-link:hover {
        background-color: rgba(63, 102, 214, .18) !important;
        color: white !important;
    }

    .nav-sidebar .nav-treeview>.nav-item>.nav-link.active {
        background-color: #3F66D6 !important;
        color: white !important;
    }

    .card {
        border-radius: 15px;
        border: none;
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }

    .form-control {
        border-radius: 10px;
        box-shadow: none;
    }

    .form-control:focus {
        border-color: #0F766E;
        box-shadow: 0 0 0 .15rem rgba(40, 167, 69, .15);
    }

    #modalCariPasien .modal-dialog {
        max-width: 625px;
        margin: 92px auto 0 auto;
    }

    #modalCariPasien .modal-content {
        border-radius: 7px;
        overflow: hidden;
    }

    #modalCariPasien .modal-header {
        padding: 18px 20px;
        background: #ffffff;
        border-bottom: 1px solid #dee2e6;
    }

    #modalCariPasien .modal-title {
        font-size: 24px;
        font-weight: 400;
        color: #2f3439;
    }

    #modalCariPasien .modal-body {
        padding: 20px;
        background: #ffffff;
    }

    #modalCariPasien #cariPasien {
        height: 42px;
        font-size: 14px;
        border-radius: 5px;
    }

    #modalCariPasien .modal-footer {
        padding: 20px;
        background: #ffffff;
    }

    #modalCariPasien .modal-footer .btn {
        min-width: 68px;
    }
</style>
