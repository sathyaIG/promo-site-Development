<div id="Download_promotion_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Download_promotion_modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="Download_promotion_modalLabel">Template Download</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="DownloadPromotype" class="forms-sample" method="post" action="{{ admin_url('PromotypeDownload') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- <div class="col-md-12 mb-3 form-group">
                        <h5 class="page-title-main">Select Promotype</h5>

                    </div> -->
                    <div class="row form-group">
                    <h5 class="page-title-main">Select Promotype</h5>
                        <div class="col-md-5 mb-3 form-group" style="padding: 0 24px">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="promotype" value="1" id="single_promotype" checked>
                                <label class="form-check-label" for="single_promotype">Single Promotype </label>
                            </div>

                        </div>
                        <div class="col-md-5 mb-3 form-group" style="padding: 0 24px">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="promotype" value="2" id="combo_promotype">
                                <label class="form-check-label" for="combo_promotype">Combo Promotype</label>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3 form-group" style="padding: 0 24px">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="promotype" value="3" id="cart_free">
                                <label class="form-check-label" for="cart_free">Combo Promotype</label>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3 form-group" style="padding: 0 24px">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="promotype" value="4" id="cart_level">
                                <label class="form-check-label" for="cart_level">Combo Promotype</label>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3 form-group" style="padding: 0 24px">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="promotype" value="5" id="free_product">
                                <label class="form-check-label" for="free_product">Combo Promotype</label>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3 form-group" style="padding: 0 24px">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="promotype" value="6" id="group_promo">
                                <label class="form-check-label" for="group_promo">Combo Promotype</label>
                            </div>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <button type="submit" class="px-4 btn btn-success btn-skew"><span class="fs-7">Download</span></button>
                        <a class="px-4 btn btn-secondary btn-skew fs-7" href="{{ admin_url('upload_promotion') }}">Cancel</a>
                    </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</div>