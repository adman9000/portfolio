<?php
/** First attempt to build in activity logginh
 * This should record all model deletes to activity log
**/

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Activity;

class MyActivityObserver
{
    /**
     * Listen to the Model created event.
     *
     * @param  User  $model
     * @return void
     */
    public function created(Model $model)
    {
         $log_info = [
            'action' => "INSERT",
            'model' => class_basename($model),
            'model_id' => $model['id'],
            'attributes' => $model->toJSON(),
            'original' => json_encode($model->getOriginal())
        ];

        return Activity::create($log_info);
    }

    /**
     * Listen to the Model deleting event.
     *
     * @param  User  $model
     * @return void
     */
    public function deleted(Model $model)
    {
        $log_info = [
            'action' => "DELETE",
            'model' => class_basename($model),
            'model_id' => $model['id'],
            'attributes' => $model->toJSON(),
            'original' => json_encode($model->getOriginal())
        ];

        return Activity::create($log_info);
    }

    public function updated(Model $model) {


        $log_info = [
            'action' => "UPDATE",
            'model' => class_basename($model),
            'model_id' => $model['id'],
            'attributes' => $model->toJSON(),
            'original' => json_encode($model->getOriginal())
        ];

        return Activity::create($log_info);

    }
}

