   {{-- MODAL FOTO LAB --}}
   <div class="modal fade" id="modalFotoLab" tabindex="-1">

       <div class="modal-dialog modal-lg modal-dialog-scrollable">

           <div class="modal-content border-0 shadow-lg">

               <div class="modal-header modal-modern">

                   <div>

                       <h5 class="modal-title font-weight-bold mb-0">

                           <i class="fas fa-file-medical mr-2"></i>
                           File Pemeriksaan

                       </h5>

                       <small id="fotoLabInfo"></small>

                   </div>

                   <button type="button" class="close text-white" data-dismiss="modal">

                       <span>&times;</span>

                   </button>

               </div>


               <div class="modal-body">

                   <input type="hidden" id="fotoIDLab">
                   <input type="hidden" id="fotoPrepID">
                   <input type="hidden" id="fotoItemIndex">

                   {{-- UPLOAD --}}
                   <div class="card border-0 bg-light mb-3">

                       <div class="card-body py-2">

                           <div class="form-row align-items-center">

                               <div class="col">

                                   <input type="file" id="fotoLabFile" class="form-control-file"
                                       accept="image/jpeg,image/png,image/webp,application/pdf" multiple>

                               </div>

                               <div class="col-auto">

                                   <button type="button" id="btnUploadFotoLab" class="btn btn-primary btn-sm"
                                       onclick="uploadFotoLab()">

                                       <i class="fas fa-upload mr-1"></i>
                                       Upload File

                                   </button>

                               </div>

                           </div>

                       </div>

                   </div>


                   {{-- FOTO --}}
                   <div id="fotoLabList">

                       <div class="text-center text-muted py-4">

                           Belum ada file.

                       </div>

                   </div>

               </div>

           </div>

       </div>

   </div>
