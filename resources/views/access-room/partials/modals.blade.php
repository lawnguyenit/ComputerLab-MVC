<div class="modal fade" id="accessModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="accessRoomForm" action="{{ route('access-room.access') }}" method="POST">
                @csrf
                <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #28a745; color: #ffffff;">
                    <h4 class="modal-title m-0" id="addModalLabel" style="color: #ffffff;">
                        <i class="fas fa-qrcode me-2"></i> Quét mã QR
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid p-0">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="room-info-container mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="room-details">
                                                <p class="mb-2">
                                                    <span class="h5 me-2 fw-bold">Truy cập phòng:</span>
                                                    <span class="fw-bold" id="roomName"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="qrCodeValue" name="qrCodeValue">
                                <input type="hidden" id="roomId" name="roomId">
                                <div class="qr-scanner-container mb-4">
                                    <div id="reader" class="rounded" style="width: 100%; border: 2px solid #eee;"></div>
                                </div>

                                <div class="results-container mb-4">
                                    <div id="qr-reader-results"></div>
                                </div>

                                <div class="instructions-container text-center">
                                    <p class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i> Hướng camera vào mã QR để quét
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='/access-room'">
                        <i class="fas fa-times me-1"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-success" id="processQrBtn" disabled>
                        <i class="fas fa-check me-1"></i> Xử lý
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var resultContainer = document.getElementById('qr-reader-results');
        var lastResult, countResults = 0;

        var html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                formatsToSupport: [ 
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.DATA_MATRIX,
                    Html5QrcodeSupportedFormats.AZTEC,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.CODE_93,
                    Html5QrcodeSupportedFormats.CODE_128
                ],
                rememberLastUsedCamera: true,
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            }
        );

        function onScanSuccess(decodedText, decodedResult) {
            if (decodedText !== lastResult) {
                ++countResults;
                lastResult = decodedText;
                document.getElementById('qrCodeValue').value = decodedText;
                document.getElementById('processQrBtn').disabled = false;
                
            }
        }

        function onScanFailure(error) {
            // Xử lý lỗi quét
            console.warn(`Lỗi quét mã QR: ${error}`);
            
            if (error === "No MultiFormat Readers were able to detect the code.") {
                resultContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Không thể nhận diện mã QR. Vui lòng đảm bảo:
                        <ul class="mb-0 mt-1">
                            <li>Mã QR rõ ràng và không bị mờ</li>
                            <li>Đặt mã QR trong khung quét</li>
                            <li>Đủ ánh sáng để camera nhận diện</li>
                            <li>Giữ camera ổn định</li>
                        </ul>
                    </div>
                `;
            }
        }

        // Thêm xử lý lỗi khi khởi tạo camera
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // Thêm nút để chuyển đổi camera (nếu có nhiều camera)
        const scannerContainer = document.getElementById('reader');
        setTimeout(() => {
            // Tìm phần tử chứa nút camera
            const cameraSelectionContainer = scannerContainer.querySelector("div:has(select)");
            
            if (cameraSelectionContainer) {
                // Thêm nút làm mới camera
                const refreshBtn = document.createElement('button');
                refreshBtn.className = 'btn btn-sm btn-info ms-2';
                refreshBtn.innerHTML = '<i class="fas fa-sync me-1"></i> Làm mới';
                refreshBtn.onclick = function() {
                    html5QrcodeScanner.clear();
                    setTimeout(() => {
                        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                    }, 300);
                };
                
                cameraSelectionContainer.appendChild(refreshBtn);
            }
        }, 1000);
        
        // Xử lý khi đóng modal
        $('#accessModal').on('hidden.bs.modal', function () {
            // Dừng scanner khi đóng modal
            html5QrcodeScanner.clear();
            // Reset các giá trị
            document.getElementById('qrCodeValue').value = '';
            document.getElementById('roomId').value = '';
            document.getElementById('roomName').textContent = '';
            document.getElementById('processQrBtn').disabled = true;
            resultContainer.innerHTML = '';
        });
    });

    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        
        .btn {
            transition: all 0.2s ease;
        }
        
        .qr-container {
            transition: all 0.3s ease;
        }
        
        .qr-container:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
    `;
    document.head.appendChild(style);
</script>

<div class="modal fade" id="closeRoomModal" tabindex="-1" role="dialog" aria-labelledby="closeRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(196, 9, 9); color: #ffffff;">
                <h4 class="modal-title m-0" id="closeRoomModalLabel" style="color: #ffffff;">
                    <i class="fas fa-door-closed me-2"></i> Xác nhận đóng cửa phòng
                </h4>
            </div>
            <div class="modal-body">
                <div class="room-info mt-3">
                    <p><strong>Phòng:</strong> <span id="closeRoomName"></span></p>
                </div>
                <p>Bạn có chắc chắn muốn đóng cửa phòng này không?</p>
                <p class="text-danger"><strong>Lưu ý:</strong> Hành động này sẽ kết thúc phiên làm việc hiện tại!</p>
            </div>
            <div class="modal-footer">
                <form id="closeRoomForm" method="POST" action="{{ route('access-room.close') }}">
                    @csrf
                    <input type="hidden" name="roomId" id="closeRoomId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-door-closed me-1"></i> Đóng phòng
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="controlModal" tabindex="-1" role="dialog" aria-labelledby="controlModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(42, 5, 122); color: #ffffff;">
                <h4 class="modal-title m-0" id="editModalLabel" style="color: #ffffff;">
                    <i class="fas fa-sliders-h me-2"></i> Điều khiển thiết bị
                </h4>
            </div>

            <div class="modal-body">
                <form id="controlForm" action="{{ route('access-room.control') }}" method="POST">
                    @csrf
                    <div class="room-info mt-3">
                        <p class="mb-2">
                            <span class="h5 me-2 fw-bold">Điều khiển thiết bị:</span>
                            <span class="fw-bold" id="namedevices"></span>
                        </p>                        
                        <input type="hidden" id="deviceId" name="deviceId">
                        <input type="hidden" id="devicecontrol" name="devicecontrol">
                    </div>
                    <div class="form-group mb-4">
                        <label for="thresholdValue" class="form-label">Ngưỡng điều khiển:</label>
                        <input type="number" 
                               class="form-control" 
                               id="deviceThreshold" 
                               name="deviceThreshold" 
                               step="0.01" 
                               required
                               placeholder="Nhập giá trị ngưỡng">
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-success" id="turnOnBtn" name="turnOnBtn" onclick="onDevice()">
                            <i class="fas fa-power-off me-2"></i> Bật
                        </button>
                        <button type="button" class="btn btn-danger" id="turnOffBtn" name="turnOffBtn" onclick="offDevice()">
                            <i class="fas fa-power-off me-2"></i> Tắt
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Đóng
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    function onDevice() {
        document.getElementById('devicecontrol').value = 'on';
        document.getElementById('controlForm').submit();
    }

    function offDevice() {
        document.getElementById('devicecontrol').value = 'off';
        document.getElementById('controlForm').submit();
    }
</script>



