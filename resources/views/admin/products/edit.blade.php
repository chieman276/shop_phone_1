@extends('admin.layouts.master')

@section('content')
<div class="container">
    <h1 class="text-center mt-3">Chỉnh sửa sản phẩm </h1>
    <form method="post" action="{{route('products.update',$product->id)}}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="container">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <div class="form-group">
                        <label>Tên sản phẩm</label>
                        <input type="text" id="name" name="productName" value="{{ $product->productName }}" class="form-control">
                        <label id="name-error" class="error" for="name" style="display: none;color:red">Vui lòng
                            nhập tên sản phẩm</label>
                    </div>
                    <div class="form-group">
                        <label>Hình Ảnh:</label>
                        <input type="text" id="image" name="image" value="{{ $product->image }}" class="form-control"
                            value="{{ old('image') }}" multiple>
                        <label id="image-error" class="error" for="image" style="display: none;color:red">Vui lòng chọn
                            hình ảnh</label>
                    </div>
                    <div class="form-group">
                        <label>Giá sản phẩm</label>
                        <input type="text" id="price" name="price" value="{{ $product->price }}" class="form-control">
                        <label id="price-error" class="error" for="price" style="display: none;color:red">Vui lòng nhập
                            giá sản phẩm</label>
                    </div>
                    <div class="form-group">
                        <label>Chi tiết sản phẩm</label>
                        <input type="text" id="description" name="description" value="{{ $product->description }}"
                            class="form-control">
                        <label id="description-error" class="error" for="description" style="display: none;color:red">Vui lòng
                            nhập
                            chi tiết sản phẩm</label>
                    </div>

                    <button class="submit btn btn-primary" style="float: right">Sửa</button>
                    <a href="{{ route('products.index')}}" class="btn btn-secondary">Trở về</a>
                </div>
                <div class="col-lg-2"></div>
            </div>
        </div>

</div>

</form>
</div>
<script>
    $(document).ready(function () {
        $('.submit').click(function () {
            var can_submit = true;
            var name = $('#name').val();
            if (name == '') {
                $('#name-error').show();
                can_submit = false;
            } else {
                $('#name-error').hide();
            }

            var image = $('#image').val();
            if (image == '') {
                $('#image-error').show();
                can_submit = false;
            } else {
                $('#image-error').hide();
            }

            var price = $('#price').val();
            if (price == '') {
                $('#price-error').show();
                can_submit = false;
            } else {
                $('#price-error').hide();
            }

            var description = $('#description').val();
            if (description == '') {
                $('#description-error').show();
                can_submit = false;
            } else {
                $('#description-error').hide();
            }

            // var category_id = $('#category_id').val();
            // if (category_id == '') {
            //     $('#category_id-error').show();
            //     can_submit = false;
            // } else {
            //     $('#category_id-error').hide();
            // }

            if (can_submit == false) {
                return false;
            }
        });
    });
</script>


@endsection