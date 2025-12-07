@extends('layouts.app')
@section('title', 'Quản lý lịch cấm sử dụng thiết bị')
@yield('css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

@section('content')

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12" style="padding-left: 0;">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title">
                    <h2>Danh sách lịch cấm sử dụng thiết bị</h2>
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
                        <table class="table table-striped table-bordered table-hover dataTables-restrictions">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Thiết bị</th>
                                    <th>Phòng</th>
                                    <th>Nội dung cấm sử dụng</th>
                                    <th>Thời gian bắt đầu</th>
                                    <th>Thời gian kết thúc</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deviceRestrictions as $index => $restriction)
                                <tr class="gradeX">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $restriction->device->ten_thiet_bi }}</td>
                                    <td>{{ $restriction->device->room->ten_phong }}</td>
                                    <td>{{ $restriction->noi_dung_cam_su_dung }}</td>
                                    <td>{{ $restriction->thoi_gian_bat_dau->format('H:i d/m/Y') }}</td>
                                    <td>{{ $restriction->thoi_gian_ket_thuc->format('H:i d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $now = now()->setTimezone('Asia/Ho_Chi_Minh');
                                            $status = '';
                                            $statusClass = '';
                                            $timebegin = $restriction->thoi_gian_bat_dau->format('Y-m-d H:i');
                                            $timeend = $restriction->thoi_gian_ket_thuc->format('Y-m-d H:i');
                                            
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
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                    </td>
                                    <td class="text-center" style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                                        <button class="btn btn-warning btn-sm edit-btn"
                                            data-tooltip="Cập nhật"
                                            data-id="{{ $restriction->id }}"
                                            data-toggle="modal"
                                            data-target="#editModal">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn"
                                            data-tooltip="Xóa"
                                            data-id="{{ $restriction->id }}"
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
@include('device_restrictions.partials.modals')
@section('js')
<script>
    $(document).ready(function() {
        // Xử lý nút sửa
        $('.edit-btn').click(function() {
            var restrictionId = $(this).attr('data-id');
            var url = '{{ route("device-restrictions.edit", ":id") }}'.replace(':id', restrictionId);

            // Get restriction data via AJAX
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.devicerestriction) {
                        $('#editForm').attr('action', '{{ route("device-restrictions.update", ":id") }}'.replace(':id', restrictionId));
                        $('#edit_id_phong').val(response.id_phong);
                        
                        setTimeout(function() {
                            // Cập nhật thông tin thiết bị
                            $('#edit_id_thietbi').val(response.devicerestriction.id_thietbi);
                            
                            // Cập nhật các thông tin khác
                            $('#edit_noi_dung_cam_su_dung').val(response.devicerestriction.noi_dung_cam_su_dung);
                            
                            // Xử lý thời gian
                            var startDateTime = new Date(response.devicerestriction.thoi_gian_bat_dau);
                            var endDateTime = new Date(response.devicerestriction.thoi_gian_ket_thuc);
                            
                            // Convert to YYYY-MM-DDThh:mm format required by datetime-local
                            $('#edit_thoi_gian_bat_dau').val(startDateTime.toISOString().slice(0,16));
                            $('#edit_thoi_gian_ket_thuc').val(endDateTime.toISOString().slice(0,16));
                            
                            // Cập nhật lại select2 sau khi đã set giá trị
                            $('#edit_id_thietbi').select2('destroy').select2({
                                dropdownParent: $('#editModal')
                            });
                        }, 300);
                    } else {
                        toastr.error('Không tìm thấy thông tin lịch cấm sử dụng thiết bị');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 404) {
                        toastr.error('Không tìm thấy dữ liệu');
                    } else {
                        console.error('Ajax error:', xhr);
                        toastr.error('Có lỗi xảy ra khi tải thông tin lịch cấm sử dụng thiết bị. Vui lòng thử lại sau.');
                    }
                }
            });
        });

        // Xử lý nút xóa
        $('.delete-btn').click(function() {
            var restrictionId = $(this).data('id');
            var url = '{{ route("device-restrictions.destroy", ":id") }}'.replace(':id', restrictionId);
            $('#deleteForm').attr('action', url);
        });
        
        // Lọc thiết bị theo phòng trong modal thêm mới
        $('#id_phong').change(function() {
            var roomId = $(this).val();
            filterDevicesByRoom(roomId, '#id_thietbi');
        });
        
        // Lọc thiết bị theo phòng trong modal chỉnh sửa
        $('#edit_id_phong').change(function() {
            var roomId = $(this).val();
            filterDevicesByRoom(roomId, '#edit_id_thietbi');
        });
        
        // Hàm lọc thiết bị theo phòng
        function filterDevicesByRoom(roomId, deviceSelectId) {
            if (roomId) {
                // Ẩn tất cả các thiết bị
                $(deviceSelectId + ' option').hide();
                // Hiển thị option mặc định
                $(deviceSelectId + ' option:first').show();
                // Hiển thị các thiết bị thuộc phòng đã chọn
                $(deviceSelectId + ' option[data-room="' + roomId + '"]').show();
                // Chọn lại option mặc định
                $(deviceSelectId).val('');
            } else {
                // Hiển thị tất cả thiết bị nếu không chọn phòng
                $(deviceSelectId + ' option').show();
            }
            // Cập nhật lại select2
            $(deviceSelectId).select2('destroy').select2({
                dropdownParent: $(deviceSelectId).closest('.modal')
            });
        }
        
        // Khởi tạo DataTables
        $('.dataTables-restrictions').DataTable({
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
                    filename: 'Danh sách lịch cấm sử dụng thiết bị', // Tên file xuất ra
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
                    <is><t>Danh sách lịch cấm sử dụng thiết bị</t></is>
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
                    title: 'Danh sách lịch cấm sử dụng thiết bị',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6] // In ra cột thứ 1, 2, 3, 4, 5, 6 (đánh số từ 0)
                    }
                },
                {
                    extend: 'print',
                    title: 'Danh sách lịch cấm sử dụng thiết bị',
                    customize: function(win) {
                        $(win.document.body).addClass('white-bg').css('font-size', '10px');
                        $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                    }
                }
            ]
        });
        
        // Khởi tạo Select2
        $('.select2').select2({
            dropdownParent: $('.modal')
        });
    });
</script>
@endsection