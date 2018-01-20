<?php
namespace App\Http\Transformers;

use App\Models\DB\api_lumen\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
  public function transform(User $user)
  {
    return [
        'id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
    ];
  }
}