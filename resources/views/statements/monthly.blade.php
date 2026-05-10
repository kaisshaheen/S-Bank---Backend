<h2>Bank Statement</h2>
<h4>Owner: {{ $owner_name }}</h4>
<p>Account: {{ $account->account_number }}</p>
<p>Period: {{ $from->toDateString() }} - {{ $to->toDateString() }}</p>

<table width="100%" border="1">
<tr>
<th>Date</th><th>Type</th><th>Amount</th><th>Description</th>
</tr>
@foreach($transactions as $t)
<tr>
<td>{{ $t->created_at->toDateString() }}</td>
<td>{{ $t->transaction_type }}</td>
<td>{{ $t->amount }}</td>
<td>{{ $t->description }}</td>
</tr>
@endforeach
</table>
