<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
     public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
      public function follow($userId)
    {
       
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;

        if ($exist || $its_me) {
            return false;
        } else {
            $this->followings()->attach($userId);
            return true;
        }
    }

    public function unfollow($userId)
    {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;

        if ($exist && !$its_me) {
            $this->followings()->detach($userId);
            return true;
        } else {
            return false;
        }
    }

    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
     public function feed_microposts()
    {
        $follow_user_ids = $this->followings()-> pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
    //ここからfavorites機能
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'user_favorites', 'user_id', 'favorites_id')->withTimestamps();
    }
/*
    public function favoriters()
    {
        return $this->belongsToMany(User::class, 'user_favorites', 'follow_id', 'favorites_id')->withTimestamps();
    }
*/    
     public function favorite($favorite_id)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_favorites($favorite_id);

        if ($exist ) {
            // 既にフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->favorites()->attach($favorite_id);
            return true;
        }
    }

    public function unfavorite($favorite_id)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_favorites($favorite_id);
       

        if ($exist) {
            // 既にフォローしていればフォローを外す
            $this->favorites()->detach($favorite_id);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }

    public function is_favorites($favorite_id)
    {
        return $this->favorites()->where('favorites_id', $favorite_id)->exists();
    }
}
