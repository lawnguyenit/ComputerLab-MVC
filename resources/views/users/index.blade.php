@extends('layouts.app')
@section('title', 'Quản lý người dùng')
@yield('css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12" style="padding-left: 0;">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title">
                    <h2>Danh sách người dùng</h2>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" data-toggle="modal" data-target="#addUserModal">
                            <i class="fa fa-plus"></i> Thêm mới
                        </button>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importUserModal">
                            <i class="fa fa-file-excel-o"></i> Import Excel
                        </button>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-users">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên tài khoản</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Khoa</th>
                                    <th>Quyền</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr class="gradeX">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $user->ten_tai_khoan }}</td>
                                    <td>{{ $user->ho_ten }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->sdt }}</td>
                                    <td>{{ $user->department->ten_khoa }}</td>
                                    <td>
                                        @php
                                            $capquyen = $userpermissions->where('id_nguoidung', $user->id); 
                                            if ($capquyen->isEmpty()) {
                                                $capquyen = 'Không có quyền';      
                                            } else {
                                                $permission = $permissions->where('id', $capquyen->first()->id_quyen)->first();
                                                $capquyen = $permission ? $permission->ten_quyen : 'Không có quyền';
                                            }
                                        @endphp
                                        <span class="badge {{ $capquyen == 'Không có quyền' ? 'badge-danger' : 'badge-primary' }}">
                                            {{ $capquyen }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                                        <button class="btn btn-warning btn-sm edit-btn"
                                            data-tooltip="Cấp quyền"
                                            data-id="{{ $user->id }}"
                                            data-toggle="modal"
                                            data-target="#editUserModal">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button class="btn btn-success btn-sm block-btn"
                                            data-tooltip="Khóa" 
                                            data-id="{{ $user->id }}"
                                            data-toggle="modal"
                                            data-target="#blockUserModal">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn"
                                            data-tooltip="Xóa"
                                            data-id="{{ $user->id }}"
                                            data-toggle="modal"
                                            data-target="#deleteUserModal">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@include('users.partials.modals')
@section('js')
<script>
    $(document).ready(function() {
        // Xử lý nút sửa người dùng
        $('.edit-btn').click(function() {
            var userId = $(this).attr('data-id');
            var url = '{{ route("users.edit", ":id") }}'.replace(':id', userId);
            
            // Cập nhật action của form
            $('#editUserForm').attr('action', '{{ route("users.update", ":id") }}'.replace(':id', userId));
            
            // Lấy thông tin người dùng qua AJAX
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#edit_ten_tai_khoan_display').text(data.ten_tai_khoan);
                    $('#edit_email_display').text(data.email);
                    $('#edit_ho_ten_display').text(data.ho_ten);
                    $('#edit_sdt_display').text(data.sdt);
                    $('#edit_khoa_display').text(data.ten_khoa);
                    if (data.id_permissions!='Chưa cấp quyền') {
                        $('#edit_id_phan_quyen').val(data.id_permissions);
                    }

                },
                error: function(xhr) {
                    console.error('Ajax error:', xhr);
                    toastr.error('Có lỗi xảy ra khi tải thông tin người dùng. Vui lòng thử lại sau.');
                }
            });
        });

        // Xử lý nút xóa
        $('.block-btn').click(function() {
            var userId = $(this).data('id');
            var url = '{{ route("users.block", ":id") }}'.replace(':id', userId);
            $('#blockUserForm').attr('action', url);
        });

        // Xử lý nút xóa
        $('.delete-btn').click(function() {
            var userId = $(this).data('id');
            var url = '{{ route("users.destroy", ":id") }}'.replace(':id', userId);
            $('#deleteUserForm').attr('action', url);
        });
        
        // Khởi tạo DataTables
        $('.dataTables-users').DataTable({
            pageLength: 10,
            responsive: true,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            order: [], // Không sắp xếp mặc định
            columnDefs: [{
                orderable: false,
                targets: [0, 7], // Không sắp xếp cột STT và Thao tác
                className: 'text-center' // Căn giữa cho cột STT
            }],
            // Tính toán lại STT dựa trên trang hiện tại
            drawCallback: function(settings) {
                var api = this.api();
                var startIndex = api.context[0]._iDisplayStart;

                api.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = startIndex + i + 1;
                });
            },
            fixedHeader: true,
            dom: "<'row mb-3'" +
                "<'col-md-4'l>" + // show entries
                "<'col-md-4 text-center'B>" + // buttons
                "<'col-md-4 d-flex justify-content-end'f>" + // search về phải
                ">" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [{
                    extend: 'copy',
                    text: 'Sao chép'
                },
                {
                    extend: 'csv',
                    text: 'CSV',
                    charset: 'utf-8',
                    bom: true //  THÊM DÒNG NÀY để fix lỗi font
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel', // Không dùng title mặc định
                    filename: 'Danh sách người dùng', // Tên file xuất ra
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6] // In ra các cột cần thiết
                    },
                    customize: function(xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var styles = xlsx.xl['styles.xml'];

                        // Đẩy toàn bộ các row xuống 1 hàng (dòng 2 trở đi)
                        $('row', sheet).each(function() {
                            var r = parseInt($(this).attr('r'));
                            $(this).attr('r', r + 1);
                            $(this).find('c').each(function() {
                                var cellRef = $(this).attr('r');
                                var col = cellRef.replace(/[0-9]/g, '');
                                var row = parseInt(cellRef.replace(/[A-Z]/g, '')) + 1;
                                $(this).attr('r', col + row);
                            });
                        });

                        // Thêm dòng tiêu đề chính ở A1
                        var title = `
            <row r="1">
                <c t="inlineStr" r="A1" s="50">
                    <is><t>Danh sách người dùng</t></is>
                </c>
            </row>
        `;
                        sheet.getElementsByTagName('sheetData')[0].innerHTML = title + sheet.getElementsByTagName('sheetData')[0].innerHTML;

                        // Merge A1 và B1
                        var mergeCells = sheet.getElementsByTagName('mergeCells')[0];
                        if (!mergeCells) {
                            mergeCells = sheet.createElement('mergeCells');
                            sheet.getElementsByTagName('worksheet')[0].appendChild(mergeCells);
                        }

                        var mergeCell = sheet.createElement('mergeCell');
                        mergeCell.setAttribute('ref', 'A1:G1');
                        mergeCells.appendChild(mergeCell);
                        mergeCells.setAttribute('count', mergeCells.getElementsByTagName('mergeCell').length);

                        // Thêm style căn giữa với font-size cho title
                        var cellXfs = styles.getElementsByTagName('cellXfs')[0];
                        var newStyle = `
                                    <xf xfId="0" applyAlignment="1" applyFont="1">
                                        <alignment horizontal="center" vertical="center"/>
                                        <font><sz val="22"/><b/></font>
                                    </xf>
                                `;
                        var headerStyle = `
                                    <xf xfId="0" applyAlignment="1" applyFont="1">
                                        <alignment horizontal="center"/>
                                        <font><sz val="16"/><b/></font>
                                    </xf>
                                `;

                        cellXfs.innerHTML += newStyle + headerStyle;

                        // Gán style: dòng 1 (title) dùng style index 50, dòng 2 (header) dùng style index 51
                        $('row[r="2"] c', sheet).attr('s', '51'); // STT, Tên đơn vị
                        $('row[r="1"] c[r="A1"]', sheet).attr('s', '50'); // Danh sách đơn vị
                    }
                },
                {
                    extend: 'pdf',
                    title: 'Danh sách người dùng',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6] // In ra các cột cần thiết
                    }
                },
                {
                    extend: 'print',
                    title: 'Danh sách người dùng',
                    customize: function(win) {
                        $(win.document.body).addClass('white-bg').css('font-size', '10px');
                        $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                    }
                }
            ]
        });
    });
</script>
@endsection