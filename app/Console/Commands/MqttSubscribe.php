<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttSubscriber;

class MqttSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đăng ký lắng nghe kênh MQTT để nhận dữ liệu cảm biến';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu lắng nghe kênh MQTT...');
        
        $subscriber = new MqttSubscriber();
        $subscriber->subscribe();
        
        return Command::SUCCESS;
    }
}