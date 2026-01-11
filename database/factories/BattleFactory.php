<?php

namespace Database\Factories;

use App\Models\Battle;
use App\Models\Deck;
use App\Models\GameMode;
use App\Models\Group;
use App\Models\LeaderClass;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Battle>
 */
class BattleFactory extends Factory
{
    protected $model = Battle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 勝率は約55%（少し勝ち越し）
        $result = $this->faker->boolean(55);

        return [
            'user_id' => User::factory(),
            'deck_id' => null,
            'my_class_id' => null,
            'opponent_class_id' => $this->faker->numberBetween(1, 7),
            'game_mode_id' => 1, // デフォルトはランクマッチ
            'rank_id' => null,
            'group_id' => null,
            'result' => $result,
            'is_first' => $this->faker->boolean(50),
            'played_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'notes' => $this->faker->optional(0.1)->sentence(),
        ];
    }

    /**
     * 特定のユーザーの対戦記録
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * 特定のデッキでの対戦記録
     */
    public function forDeck(Deck $deck): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $deck->user_id,
            'deck_id' => $deck->id,
            'my_class_id' => null,
        ]);
    }

    /**
     * 2Pick用（デッキなし、自クラス指定）
     */
    public function twoPick(): static
    {
        return $this->state(fn (array $attributes) => [
            'deck_id' => null,
            'my_class_id' => $this->faker->numberBetween(1, 7),
            'game_mode_id' => 5, // 2Pick
        ]);
    }

    /**
     * ランクマッチ用（ランク・グループ付き）
     */
    public function rankMatch(?int $rankId = null, ?int $groupId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'game_mode_id' => 1, // RANK
            'rank_id' => $rankId ?? $this->faker->numberBetween(1, 26),
            'group_id' => $groupId ?? $this->faker->numberBetween(1, 5),
        ]);
    }

    /**
     * フリーマッチ用
     */
    public function freeMatch(): static
    {
        return $this->state(fn (array $attributes) => [
            'game_mode_id' => 2, // FREE
            'rank_id' => null,
            'group_id' => null,
        ]);
    }

    /**
     * 今日の対戦
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'played_at' => $this->faker->dateTimeBetween('today', 'now'),
        ]);
    }

    /**
     * 今週の対戦
     */
    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'played_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * 勝利
     */
    public function win(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => true,
        ]);
    }

    /**
     * 敗北
     */
    public function lose(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => false,
        ]);
    }

    /**
     * 先攻
     */
    public function first(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_first' => true,
        ]);
    }

    /**
     * 後攻
     */
    public function second(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_first' => false,
        ]);
    }
}
