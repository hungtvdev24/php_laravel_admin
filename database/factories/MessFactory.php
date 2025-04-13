<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessFactory extends Factory
{
    public function definition()
    {
        return [
            'sender_id' => User::factory(), // Người gửi giả lập
            'receiver_id' => User::factory(), // Người nhận giả lập
            'content' => $this->faker->paragraph(), // Nội dung ngẫu nhiên
            'is_read' => $this->faker->boolean(), // Trạng thái đã đọc (true/false)
        ];
    }
}