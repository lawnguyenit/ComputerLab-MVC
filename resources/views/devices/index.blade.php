@extends('layouts.app')
@section('title', 'Quản lý thiết bị')

@yield('css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12" style="padding-left: 0;">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title">
                    <h2>Danh sách thiết bị</h2>
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
                        <table class="table table-striped table-bordered table-hover dataTables-devices">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên thiết bị</th>
                                    <th>Mã số</th>
                                    <th>Loại thiết bị</th>
                                    <th>Phòng</th>
                                    <th>Ngưỡng điều khiển</th>
                                    <th>Trạng thái</th>
                                    <th>Khóa thiết bị</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($devices as $device)
                                <tr class="gradeX">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $device->ten_thiet_bi }}</td>
                                    <td>{{ $device->ma_so }}</td>
                                    <td>{{ $device->deviceType->ten_loai_thiet_bi }}</td>
                                    <td>{{ $device->room->ten_phong }}</td>
                                    <td>{{ $device->nguong_dieu_khien ?? 'NULL' }}</td>
                                    <td>
                                        <span class="badge {{ $device->status->id == 1 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $device->status->ten_trang_thai }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            if ($khoathietbi->contains('id_thietbi', $device->id)) {
                                                $now = now()-> setTimezone('Asia/Ho_Chi_Minh');
                                                $timebegin = $khoathietbi->first()->thoi_gian_bat_dau->format('Y-m-d H:i');
                                                $timeend = $khoathietbi->first()->thoi_gian_ket_thuc->format('Y-m-d H:i');
                                                $now = now()->setTimezone('Asia/Ho_Chi_Minh');
                                                $status = '';
                                                $statusClass = '';
                                                
                                                if ($now < $timebegin) {
                                                    $status = 'Chưa đến';
                                                    $statusClass = 'badge-warning';
                                                } elseif ( $now > $timeend) {
                                                    $status = 'Đã kết thúc';
                                                    $statusClass = 'badge-danger';
                                                } else {
                                                    $status = 'Đang diễn ra';
                                                    $statusClass = 'badge-success';
                                                }
                                            } else {
                                                $status = 'Không khóa';
                                                $statusClass = 'badge-secondary';
                                            }
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                    </td>
                                    <td class="text-center" style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                                        <button class="btn btn-warning btn-sm edit-btn"
                                            data-tooltip="Cập nhật"
                                            data-id="{{ $device->id }}"
                                            data-toggle="modal"
                                            data-target="#editModal">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn"
                                            data-tooltip="Xóa"
                                            data-id="{{ $device->id }}"
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
@include('devices.partials.modals')
@section('js')
<script>
    $(document).ready(function() {
        // Xử lý nút sửa thiết bị
        $('.edit-btn').click(function() {
            var deviceId = $(this).attr('data-id');
            var url = '{{ route("devices.edit", ":id") }}'.replace(':id', deviceId);
            
            // Get device data via AJAX
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response && response.ten_thiet_bi) {
                        // Populate edit form with device data
                        $('#editForm').attr('action', '{{ route("devices.update", ":id") }}'.replace(':id', deviceId));
                        $('#edit_ten_thiet_bi').val(response.ten_thiet_bi);
                        $('#edit_ma_so').val(response.ma_so);
                        $('#edit_id_loaithietbi').val(response.id_loaithietbi);
                        $('#edit_nguong_dieu_khien').val(response.nguong_dieu_khien);
                        $('#edit_id_phong').val(response.id_phong);
                        $('#edit_id_trangthai').val(response.id_trangthai);
                    } else {
                        toastr.error('Không tìm thấy thông tin thiết bị');
                    }
                },
                error: function(xhr) {
                    // Show error message if request fails
                    console.error('Ajax error:', xhr);
                    toastr.error('Có lỗi xảy ra khi tải thông tin thiết bị. Vui lòng thử lại sau.');
                }
            });
        });

        // Xử lý nút xóa
        $('.delete-btn').click(function() {
            var deviceId = $(this).data('id');
            var url = '{{ route("devices.destroy", ":id") }}'.replace(':id', deviceId);
            $('#deleteForm').attr('action', url);
        });
        
        // Khởi tạo DataTables
        $('.dataTables-devices').DataTable({
            pageLength: 10,
            responsive: true,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "Tất cả"]
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
                    filename: 'Danh sách thiết bị', // Tên file xuất ra
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
                    <is><t>Danh sách thiết bị</t></is>
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
                        $('row[r="2"] c', sheet).attr('s', '51'); // STT, Tên thiết bị, ...
                        $('row[r="1"] c[r="A1"]', sheet).attr('s', '50'); // Danh sách thiết bị
                    }

                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    title: 'Danh sách thiết bị',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5] // In ra các cột cần thiết
                    }
                },
                {
                    extend: 'print',
                    text: 'In',
                    title: 'Danh sách thiết bị',
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