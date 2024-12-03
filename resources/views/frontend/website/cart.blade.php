@extends('frontend.layouts.master')
@section('content')
<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
<div class="row">
    <div class="col-lg-2"></div>
    <div class="col-lg-10">
        <div class="session-title">
            @if (Session::has('success'))
        </div>
        <div class="text text-success">
            <h3><b>{{session::get('success')}}</b></h3>
        </div>
        @endif
        @if (Session::has('error'))
        <div class="text text-danger">
            <h3><b>{{session::get('error')}}</b></h3>
        </div>
        @endif
    </div>
</div>
</div>
<div class="container">
    <table id="cart" class="table table-hover table-condensed">
        <form action="{{ route('remove_all_product')}}" style="display:inline" method="post">
            @csrf
            @method('delete')

            @if ($errors->any())
            <p style="color:red">{{ $errors->first('id') }}</p>
            @endif
            <thead>
                <tr>
                    <th style="width:50%">Sản Phẩm</th>
                    <th style="width:10%">Giá</th>
                    <th style="width:8%">Số Lượng</th>
                    <th style="width:22%" class="text-center">Tổng Tiền</th>
                    <th style="width:10%"><button onclick="return confirm('Xóa tất cả sản phẩm trong giỏ hàng ?')"
                            type="submit" class="btn btn-danger sent">Xóa Nhanh</button></th>
                </tr>
            </thead>
            <tbody>
                @php
                $total = 0;
                @endphp
                @if(session('cart'))
                @foreach(session('cart') as $id => $details)
                @php $total += $details['price'] * $details['quantity']; @endphp
                <tr data-id="{{ $id }}" class="product_hide">
                    <td data-th="Product">
                        <div class="row">
                            <div class="col-sm-3 hidden-xs"><img src="{{ $details['image'] }}" width="100" height="100"
                                    class="img-responsive" /></div>
                            <div class="col-sm-9">
                                <h4 class="nomargin">{{ $details['productName'] }}</h4>
                            </div>
                        </div>
                    </td>
                    <td data-th="Price">{{ number_format($details['price']) }} đ</td>
                    <td data-th="Quantity">
                        <input type="number" value="{{ $details['quantity'] }}"
                            class="form-control quantity update-cart" />
                    </td>
                    <td data-th="Subtotal" class="text-center">{{ number_format($details['price'] *
                        $details['quantity']) }} đ</td>
                    <td class="actions" data-th="">
                        <button class="btn btn-danger btn-sm remove-from-cart"><i class="fa fa-trash-o"></i></button>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <tbody>
                        <td class="text-right">
                            <form id="form-discountName">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <select class="value_discountName" >
                                            <option value="">Các mã giảm giá</option>
                                            @foreach($discounts as $discount)
                                            @if($discount->user_id == $userAll->id)
                                            <option value="{{$discount->discountName}}"> <button>{{$discount->discountName}}</button></option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control discountName" name="discountName"
                                            value="" placeholder="Nhập mã giảm giá">
                                        <h4 style="color:red; float:left" class="validate_discountName"> &emsp; &emsp; Vui lòng nhập mã giảm
                                                giá </h4>
                                    </div>
                                    <div class="col-lg-1">
                                        <button class="btn btn-secondary btn_discount" style="color:white"
                                            type="button">Áp dụng</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                        <td colspan="5" class="text-right result_total">
                            <h3 style="color:red ;"><strong>Tổng số tiền:{{ number_format( $total)}} đ</strong></h3>
                        </td>
                    </tbody>
                </tr>
                <tr>
                    <td colspan="5" class="text-right">
                        <a href="{{ route('websiteProduct') }}" class="btn btn-warning"><i class="fa fa-angle-left"></i>
                            Trở về</a>
                        <a href="{{ route('checkout') }}" class="btn btn-success"> Mua <i
                                class="fa fa-angle-right"></i></a>
                    </td>
                </tr>
            </tfoot>
        </form>
    </table>

    @endsection
    @section('scripts')

    <script type="text/javascript">
        $('.validate_discountName').hide();
        $(".value_discountName").change(function () {
        var discount_name = this.value;
        var inputVal = $('.discountName').val(discount_name);
        });
        $(".btn_discount").click(function (e) {
            e.preventDefault();
            var ele = $(this);
            var data_post = $(".discountName").val();
            var data_total = $('.data_total').text();
            if (data_post == "") {
                $('.validate_discountName').show();
                return false;
            }
            if (confirm("Sử dụng mã giảm giá?")) {
                $.ajax({
                    url: '{{ route('result_discounts') }}',
                    method: "patch",
                    data: {
                        getData: data_post,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        window.location.reload();
                    }
                });
            };
        });

        $(".update-cart").change(function (e) {
            e.preventDefault();
            var ele = $(this);
            $.ajax({
                url: '{{ route('update.cart') }}',
                method: "patch",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: ele.parents("tr").attr("data-id"),
                    quantity: ele.parents("tr").find(".quantity").val()
                },
                success: function (response) {
                    window.location.reload();
                }
            });
        });

        $(".remove-from-cart").click(function (e) {
            e.preventDefault();
            var ele = $(this);
            if (confirm("Bạn có chắc muốn xóa khỏi giỏ hàng?")) {
                $.ajax({
                    url: '{{ route('remove.from.cart') }}',
                    method: "DELETE",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: ele.parents("tr").attr("data-id")
                    },

                    success: function (response) {
                        window.location.reload();
                    }
                });
            }
        });
    </script>

    @endsection