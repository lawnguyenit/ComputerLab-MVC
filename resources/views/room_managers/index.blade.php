@extends('layouts.app')
@section('title', 'Quản lý phòng')
@yield('css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

@section('content')

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12" style="padding-left: 0;">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title">
                    <h2>Danh sách quản lý phòng</h2>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" data-toggle="modal" data-target="#addModal">
                            <i class="fa fa-plus"></i> Thêm mới
                        </button>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importModal">
                            <i class="fa fa-file-excel-o"></i> Import Excel
                        </button>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-room-managers">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Người quản lý</th>
                                    <th>Phòng</th>
                                    <th>Vị trí phòng</th>
                                    <th>Khu vực</th>
                                    <th>Mô tả</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roomManagers as $manager)
                                <tr class="gradeX">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $manager->user->ho_ten }}</td>
                                    <td>{{ $manager->room->ten_phong }}</td>
                                    <td>{{ $manager->room->vi_tri }}</td>
                                    <td>{{ $manager->room->khu_vuc }}</td>
                                    <td>{{ $manager->mo_ta }}</td>
                                    <td class="text-center" style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                                        <button class="btn btn-warning btn-sm edit-btn"
                                            data-tooltip="Cập nhật"
                                            data-id="{{ $manager->id }}"
                                            data-toggle="modal"
                                            data-target="#editModal">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn"
                                            data-tooltip="Xóa"
                                            data-id="{{ $manager->id }}"
                                            data-toggle="modal"
                                            data-target="#deleteModal">
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
@include('room_managers.partials.modals')
@section('js')
<script>
    $(document).ready(function() {
        // Xử lý nút sửa quản lý phòng
        $('.edit-btn').click(function() {
            var managerId = $(this).attr('data-id');
            var url = '{{ route("room-managers.edit", ":id") }}'.replace(':id', managerId);
            
            // Get room manager data via AJAX
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response && response.roommanager) {
                        // Populate edit form with room manager data
                        $('#editForm').attr('action', '{{ route("room-managers.update", ":id") }}'.replace(':id', managerId));
                        $('#edit_id_nguoidung').val(response.roommanager.id_nguoidung);
                        $('#edit_id_phong').val(response.roommanager.id_phong); 
                        $('#edit_mo_ta').val(response.roommanager.mo_ta);
                    } else {
                        toastr.error('Không tìm thấy thông tin quản lý phòng');
                    }
                },
                error: function(xhr) {
                    // Show error message if request fails 
                    console.error('Ajax error:', xhr);
                    toastr.error('Có lỗi xảy ra khi tải thông tin quản lý phòng. Vui lòng thử lại sau.');
                }
            });
        });

        // Xử lý nút xóa
        $('.delete-btn').click(function() {
            var managerId = $(this).data('id');
            var url = '{{ route("room-managers.destroy", ":id") }}'.replace(':id', managerId);
            $('#deleteForm').attr('action', url);
        });
        
        // Khởi tạo DataTables
        $('.dataTables-room-managers').DataTable({
            pageLength: 10,
            responsive: true,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            order: [], // Không sắp xếp mặc định
            columnDefs: [{
                orderable: false,
                targets: [0, 6], // Không sắp xếp cột STT và Thao tác
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
                    extend: 'copy'
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
                    filename: 'Danh sách quản lý phòng', // Tên file xuất ra
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5] // In ra các cột cần thiết
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
                    <is><t>Danh sách quản lý phòng</t></is>
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
                        mergeCell.setAttribute('ref', 'A1:F1');
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
                    title: 'Danh sách quản lý phòng',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5] // In ra cột thứ 1, 2, 3, 4, 5 (đánh số từ 0)
                    }
                },
                {
                    extend: 'print',
                    title: 'Danh sách quản lý phòng',

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