<?php 

namespace Ababilitworld\FinanceManager\Repositories;

use Ababilitworld\FinanceManager\Interfaces\TransactionRepositoryInterface;

class WordPress implements TransactionRepositoryInterface 
{
    public function getTransactions(array $criteria): \Generator 
    {
        $args = [
            'post_type' => 'transaction',
            'meta_query' => $criteria['meta_query'] ?? [],
            'tax_query' => $criteria['tax_query'] ?? [],
            'posts_per_page' => -1,
        ];
        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                yield [
                    'ID' => get_the_ID(),
                    'title' => get_the_title(),
                    'cost' => get_post_meta(get_the_ID(), 'cost', true),
                    'paid' => get_post_meta(get_the_ID(), 'paid', true),
                    'due' => get_post_meta(get_the_ID(), 'due', true),
                    'transaction_date' => get_post_meta(get_the_ID(), 'transaction_date', true),
                ];
            }
            wp_reset_postdata();
        }
    }

    public function addTransaction(array $data): int {
        $postId = wp_insert_post([
            'post_type' => 'transaction',
            'post_title' => $data['title'],
            'post_status' => 'publish',
        ]);

        if ($postId) {
            foreach ($data as $key => $value) {
                if ($key !== 'title') {
                    update_post_meta($postId, $key, $value);
                }
            }
        }

        return $postId;
    }
}
