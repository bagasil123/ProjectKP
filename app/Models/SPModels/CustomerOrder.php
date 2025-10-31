<?php

namespace App\Models\SPModels;

use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    /**
     * The table associated with the model.
     * This should match the table name created by your migration file.
     * @var string
     */
    protected $table = 'customer_orders';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the pelanggan that owns the customer order.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Get the details for the customer order.
     */
    public function details()
    {
        return $this->hasMany(CustomerOrderDetail::class, 'customer_order_id');
    }
}
