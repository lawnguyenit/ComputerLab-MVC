<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Room;
use App\Models\Sensor;
use App\Models\SensorData;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailySensorDataReport;
use App\Models\DataList;
use App\Models\RoomManager;
use League\CommonMark\Extension\CommonMark\Node\Block\ListData;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SendDailySensorDataReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-sensor-data {--date= : Ngày cần tổng hợp dữ liệu (định dạng Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tổng hợp dữ liệu cảm biến trong ngày và gửi báo cáo qua email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now('Asia/Ho_Chi_Minh');
        $startDate = $date->copy()->timezone('Asia/Ho_Chi_Minh')->startOfDay();
        $endDate = $date->copy()->timezone('Asia/Ho_Chi_Minh')->endOfDay();
        
        $this->info('Đang tổng hợp dữ liệu cảm biến cho ngày ' . $date->format('d/m/Y'));
        
        // Lấy danh sách phòng
        $rooms = Room::all();
        
        foreach ($rooms as $room) {
            $this->info('Đang xử lý dữ liệu cho phòng: ' . $room->ten_phong);
            
            // Lấy danh sách cảm biến trong phòng
            $sensors = Sensor::where('id_phong', $room->id)->get();
            
            if ($sensors->isEmpty()) {
                $this->warn('Không có cảm biến nào trong phòng ' . $room->ten_phong);
                continue;
            }
            
            // Tạo file Excel
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator('Hệ thống quản lý phòng máy')
                ->setLastModifiedBy('Hệ thống quản lý phòng máy')
                ->setTitle('Báo cáo dữ liệu cảm biến ngày ' . $date->format('d/m/Y'))
                ->setSubject('Báo cáo dữ liệu cảm biến')
                ->setDescription('Báo cáo dữ liệu cảm biến phòng ' . $room->ten_phong . ' ngày ' . $date->format('d/m/Y'));
            
            // Tạo sheet tổng quan
            $overviewSheet = $spreadsheet->getActiveSheet();
            $overviewSheet->setTitle('Tổng quan');
            
            // Thiết lập tiêu đề cho sheet tổng quan
            $overviewSheet->setCellValue('A1', 'BÁO CÁO DỮ LIỆU CẢM BIẾN');
            $overviewSheet->setCellValue('A2', 'Phòng: ' . $room->ten_phong);
            $overviewSheet->setCellValue('A3', 'Ngày: ' . $date->format('d/m/Y'));
            $overviewSheet->setCellValue('A5', 'STT');
            $overviewSheet->setCellValue('B5', 'Tên cảm biến');
            $overviewSheet->setCellValue('C5', 'Loại cảm biến');
            $overviewSheet->setCellValue('D5', 'Giá trị trung bình');
            $overviewSheet->setCellValue('E5', 'Giá trị thấp nhất');
            $overviewSheet->setCellValue('F5', 'Giá trị cao nhất');
            $overviewSheet->setCellValue('G5', 'Đơn vị lưu trữ');
            $overviewSheet->setCellValue('H5', 'Số lượng dữ liệu');
            
            // Định dạng tiêu đề
            $overviewSheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(16);
            $overviewSheet->getStyle('A5:H5')->getFont()->setBold(true);
            $overviewSheet->getStyle('A5:H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
            
            // Thiết lập độ rộng cột
            $overviewSheet->getColumnDimension('A')->setWidth(5);
            $overviewSheet->getColumnDimension('B')->setWidth(40);
            $overviewSheet->getColumnDimension('C')->setWidth(40);
            $overviewSheet->getColumnDimension('D')->setWidth(25);
            $overviewSheet->getColumnDimension('E')->setWidth(25);
            $overviewSheet->getColumnDimension('F')->setWidth(25);
            $overviewSheet->getColumnDimension('G')->setWidth(30);
            $overviewSheet->getColumnDimension('H')->setWidth(25);

            
            // Dòng bắt đầu dữ liệu
            $row = 6;
            
            // Xử lý từng cảm biến
            $sensors =Sensor::where('id_phong', $room->id)->get(); 
            $dem=0;  

            foreach ($sensors as $index => $sensor) {
                // Lấy dữ liệu cảm biến trong ngày
                $sensorData = DataList::where('id_cambien', $sensor->id)
                    ->whereBetween('thoi_gian_thu_thap', [$startDate, $endDate])
                    ->get();

                
                if ($sensorData->isEmpty()) {
                    $this->warn('Không có dữ liệu cho cảm biến ' . $sensor->ten_cam_bien);
                    continue;
                } else {
                    $dem++;
                }
                
                // Tính toán các giá trị thống kê
                if ($sensor->sensorType->ten_loai_cam_bien == 'Cảm biến nhiệt độ - độ ẩm') {
                    $nhietdo = DataList::where('id_cambien', $sensor->id)
                        ->where('id_donviluutru', 3)
                        ->whereBetween('thoi_gian_thu_thap', [$startDate, $endDate])
                        ->with('storageUnit')
                        ->get();
                    $doam = DataList::where('id_cambien', $sensor->id)
                        ->where('id_donviluutru', 4)
                        ->whereBetween('thoi_gian_thu_thap', [$startDate, $endDate])
                        ->with('storageUnit')
                        ->get();
                    $avgValue = $nhietdo->avg('du_lieu_thu_thap').' - '.$doam->avg('du_lieu_thu_thap');
                    $minValue = $nhietdo->min('du_lieu_thu_thap').' - '.$doam->min('du_lieu_thu_thap');
                    $maxValue = $nhietdo->max('du_lieu_thu_thap').' - '.$doam->max('du_lieu_thu_thap');
                    $donvi = $nhietdo->first()->storageUnit->ten_don_vi_luu_tru.' - '.$doam->first()->storageUnit->ten_don_vi_luu_tru;
                    $count = $nhietdo->count().'-'.$doam->count();
                } else {
                    $avgValue = $sensorData->avg('du_lieu_thu_thap');
                    $minValue = $sensorData->min('du_lieu_thu_thap');
                    $maxValue = $sensorData->max('du_lieu_thu_thap');
                    $donvi = $sensorData->first()->storageUnit->ten_don_vi_luu_tru;
                    $count = $sensorData->count();
                }

                
                // Thêm dữ liệu vào sheet tổng quan
                $overviewSheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $overviewSheet->setCellValue('A' . $row, $dem);
                $overviewSheet->setCellValue('B' . $row, $sensor->ten_cam_bien);
                $overviewSheet->setCellValue('C' . $row, $sensor->sensorType->ten_loai_cam_bien);
                $overviewSheet->setCellValue('D' . $row, $avgValue);
                $overviewSheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $overviewSheet->setCellValue('E' . $row, $minValue);
                $overviewSheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $overviewSheet->setCellValue('F' . $row, $maxValue);
                $overviewSheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $overviewSheet->setCellValue('G' . $row, $donvi);
                $overviewSheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $overviewSheet->setCellValue('H' . $row, $count);
                $overviewSheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Tạo sheet chi tiết cho cảm biến
                $sensorSheet = $spreadsheet->createSheet();
                $sensorSheet->setTitle(substr($sensor->ten_cam_bien, 0, 31)); // Giới hạn tên sheet tối đa 31 ký tự
                
                // Thiết lập tiêu đề cho sheet chi tiết
                $sensorSheet->setCellValue('A1', 'DỮ LIỆU CHI TIẾT CẢM BIẾN: ' . $sensor->ten_cam_bien);
                $sensorSheet->setCellValue('A2', 'Loại cảm biến: ' . $sensor->sensorType->ten_loai_cam_bien);
                $sensorSheet->setCellValue('A5', 'STT');
                $sensorSheet->setCellValue('B5', 'Thời gian');
                $sensorSheet->setCellValue('C5', 'Giá trị');
                $sensorSheet->setCellValue('D5', 'Đơn vị lưu trữ: ');
                
                // Định dạng tiêu đề
                $sensorSheet->getStyle('A1:C1')->getFont()->setBold(true)->setSize(14);
                $sensorSheet->getStyle('A5:D5')->getFont()->setBold(true);
                $sensorSheet->getStyle('A5:D5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
                
                // Thiết lập độ rộng cột
                $sensorSheet->getColumnDimension('A')->setWidth(5);
                $sensorSheet->getColumnDimension('B')->setWidth(20);
                $sensorSheet->getColumnDimension('C')->setWidth(15);
                $sensorSheet->getColumnDimension('D')->setWidth(20);
                
                // Dòng bắt đầu dữ liệu chi tiết
                $detailRow = 6;
                
                // Thêm dữ liệu chi tiết
                foreach ($sensorData as $dataIndex => $data) {
                    $overviewSheet->getStyle('A' . $detailRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sensorSheet->setCellValue('A' . $detailRow, $dataIndex + 1);
                    $overviewSheet->getStyle('B' . $detailRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sensorSheet->setCellValue('B' . $detailRow, $data->thoi_gian_thu_thap->format('d/m/Y H:i:s'));
                    $overviewSheet->getStyle('C' . $detailRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sensorSheet->setCellValue('C' . $detailRow, $data->du_lieu_thu_thap);
                    $overviewSheet->getStyle('D' . $detailRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sensorSheet->setCellValue('D' . $detailRow, $data->storageUnit->ten_don_vi_luu_tru);
                    $detailRow++;
                }
                
                $row++;
            }
            
            // Lưu file Excel
            $fileName = 'bao-cao-du-lieu-cam-bien-' . $room->ten_phong . '-' . $date->format('Y-m-d') . '.xlsx';
            $filePath = storage_path('app/public/reports/' . $fileName);
            
            // Đảm bảo thư mục tồn tại
            if (!file_exists(storage_path('app\public\reports'))) {
                mkdir(storage_path('app\public\reports'), 0755, true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            
            $this->info('Đã tạo file báo cáo: ' . $filePath);
            
            // Tìm người quản lý phòng
            // Get room managers
            $managers = RoomManager::where('id_phong', $room->id)
                ->with('user') // Eager load user relationship
                ->get()
                ->pluck('user'); // Get associated users

            if ($managers->isEmpty()) {
                $this->warn('Không có người quản lý phòng '. $room->ten_phong);
                continue;
            }

            // Send email to each manager
            foreach ($managers as $manager) {
                if ($manager && $manager->email) {
                    Mail::to($manager->email)->send(new DailySensorDataReport($room, $date, $filePath));
                    $this->info('Đã gửi email báo cáo đến: ' . $manager->email);
                }
            }
        }
        
        $this->info('Hoàn thành tổng hợp dữ liệu và gửi báo cáo');
        
        return Command::SUCCESS;
    }
}