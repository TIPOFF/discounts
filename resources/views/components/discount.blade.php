<div {{ $attributes }}>
    <div>{{ $deduction->name }}</div>
    <div>Code {{ $deduction->getCode() }}</div>
    <div><x-tipoff-money label="Discount" :amount="$deduction->getAmount()"/></div>
</div>
