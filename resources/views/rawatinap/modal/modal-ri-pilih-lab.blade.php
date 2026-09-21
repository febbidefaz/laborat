    {{-- Modal Tambah Lab --}}
    <div class="modal fade" id="modalPilihLab" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header modal-modern">
                    <h5 class="modal-title">
                        <i class="fas fa-microscope mr-2"></i>
                        Pilih Pemeriksaan Laboratorium
                    </h5>

                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <input type="text" id="cariMasterLab" class="form-control mb-3"
                        placeholder="Cari pemeriksaan...">

                    <div class="table-responsive">

                        <table class="table table-bordered table-sm">

                            <thead>
                                <tr>
                                    <th width="60">Pilih</th>
                                    <th>Pemeriksaan</th>
                                    <th>Nilai Normal</th>
                                    <th width="140" class="text-right">
                                        Biaya
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="masterLabBody">

                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Memuat data...
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        </div>
    </div>
