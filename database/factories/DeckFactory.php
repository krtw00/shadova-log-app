<?php

namespace Database\Factories;

use App\Models\Deck;
use App\Models\LeaderClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deck>
 */
class DeckFactory extends Factory
{
    protected $model = Deck::class;

    /**
     * デッキ名のテンプレート
     */
    protected static array $deckNames = [
        1 => ['フェアリーエルフ', 'アグロエルフ', 'コントロールエルフ', 'ミッドレンジエルフ', 'OTKエルフ'],
        2 => ['連携ロイヤル', 'アグロロイヤル', 'ミッドレンジロイヤル', 'コントロールロイヤル', '進化ロイヤル'],
        3 => ['秘術ウィッチ', 'スペルウィッチ', 'マナリアウィッチ', 'バーンウィッチ', 'コントロールウィッチ'],
        4 => ['ランプドラゴン', 'アグロドラゴン', 'ディスカドラゴン', '原初ドラゴン', 'フェイスドラゴン'],
        5 => ['アグロナイトメア', 'ミッドレンジナイトメア', 'コントロールナイトメア', '自傷ナイトメア', '葬送ナイトメア'],
        6 => ['回復ビショップ', 'アミュレットビショップ', 'エイラビショップ', 'コントロールビショップ', 'アグロビショップ'],
        7 => ['AFネメシス', '共鳴ネメシス', 'コントロールネメシス', 'ミッドレンジネメシス', 'アグロネメシス'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $leaderClassId = $this->faker->numberBetween(1, 7);
        $names = self::$deckNames[$leaderClassId] ?? ['デッキ'];

        return [
            'user_id' => User::factory(),
            'leader_class_id' => $leaderClassId,
            'name' => $this->faker->randomElement($names),
        ];
    }

    /**
     * 特定のユーザーのデッキ
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * 特定のクラスのデッキ
     */
    public function forClass(int $leaderClassId): static
    {
        $names = self::$deckNames[$leaderClassId] ?? ['デッキ'];

        return $this->state(fn (array $attributes) => [
            'leader_class_id' => $leaderClassId,
            'name' => $this->faker->randomElement($names),
        ]);
    }
}
