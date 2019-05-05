<?php

namespace App\Jobs;

use App\Http\Services\RightOrderService;
use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class LockStockWatcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $end_time;
    protected $interval_time;
    protected $rights_list;
    protected $activity_id;

    /**
     * LockStockWatcher constructor.
     * @param $activity 活动查询语句
     */
    public function __construct($activity)
    {
        $this->end_time = strtotime($activity->end_time);
        // 8秒轮询
        $this->interval_time = 8;
        // 保存需要轮询的权益id
        $this->rights_list = [];
        foreach ($activity->rights()->get() as $item) {
            array_push($this->rights_list, $item->id);
        }
        $this->activity_id = $activity->id;
        Log::debug('活动ID为' . $this->activity_id . '，开始监控权益情况，结束时间是' . $activity->end_time);
        Log::debug($this->rights_list);
    }


    /**
     * @param RightOrderService $orderService
     */
    public function handle(RightOrderService $orderService)
    {
        set_time_limit(0);
        while (time() < $this->end_time) {
            Log::debug("活动ID为" . $this->activity_id . '，正在监控权益情况');
            // 若权益在更新，则刷新权益数组，且释放锁
            if ($orderService->isRightHadUpdate()) {
                $activity  = Activity::find($this->activity_id);
                $newRights = $activity->rights()->get();
                if (count($newRights) == 0) {
                    Log::debug('由于更新后权益没了，所以停止监听');
                    break;
                }
                $this->rights_list = [];
                foreach ($newRights as $item) {
                    array_push($this->rights_list, $item->id);
                }
                $orderService->delRightUpdate();
                $orderService->finishRightUpdate();
            }
            // 查找过期锁定期间
            $orderService->findExpired($this->rights_list, $hasList);
            if (!$hasList && !$orderService->hasStock($this->rights_list) && !$orderService->inQueueSet($this->activity_id)) {
                Log::debug('竟然库存列表跟支付列表都清空了，赶集看一下是不是bug，当然，也有可能是人为清空了队列列表');
                break;
            }
            sleep($this->interval_time);
        }
        // 活动已结束
        Log::debug('活动已结束');
        // 退出队列集合
        $orderService->delQueueSet($this->activity_id);
    }

    // 队列失败处理
    public function failed(\Exception $exception)
    {

    }
}
