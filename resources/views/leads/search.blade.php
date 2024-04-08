<p>hello world</p>
<style type="text/css">
		.pagination li{
			float: left;
			list-style-type: none;
			margin:5px;
		}
	</style>

 <form action="/leads/search" method="GET">
		<input type="text" name="email_search" placeholder="Cari Email" value="{{ old('email_search') }}">
		<input type="submit" value="CARI">
	</form>
	<form action="/leads/search" method="GET">
		<input type="text" name="source_search" placeholder="Cari Source" value="{{ old('source_search') }}">
		<input type="submit" value="CARI">
	</form>
	
	<table border="1">
		<tr>
			<th>Nama</th>
			<th>Email</th>
			<th>Phone</th>
			<th>Source</th>
			<th>Tanggal Daftar</th>
		</tr>
	@foreach($result as $p)
		<tr>
			<td>{{ $p->name }}</td>
			<td>{{ $p->email }}</td>
			<td>{{ $p->phone }}</td>
			<td>{{ $p->source }} {{ $p->medium }} {{ $p->campaign }}</td>
			<td>{{ $p->created_at }}</td>
	</tr>
	@endforeach
	</table>