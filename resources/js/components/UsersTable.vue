<template>
	<div class="row">
		<div class="col-10">
			<div class="form-group">
				<input type="text" class="form-control" placeholder="Pesquisa..." @keyup="search($event)" v-model="searchTerm">
			</div>
		</div>
		<div class="col-2 text-right">
			<div class="form-group">
				<a href="/dashboard/users/create" class="btn btn-md btn-default text-info"><i class="fas fa-plus"></i> NOVO</a>
			</div>
		</div>
		<div class="col">
			<div ref="usersTable" class="table table-striped table-responsive-sm"></div>
		</div>
	</div>
</template>

<script>
	export default {
		data: function () {
			return {
				tabulator: null, //variable to hold your table
				tableData: this.users, //data for table to display
				searchTerm: null
			}
		},
		props: ['users'],
		watch:{
			//update table if data changes
			tableData:{
				handler: function (newData) {
					this.tabulator.replaceData(newData);
				},
				deep: true,
			}
		},
		mounted() {
			//instantiate Tabulator when element is mounted
			this.tabulator = new Tabulator(this.$refs.usersTable, {
				data: this.tableData, //link data to table
				placeholder: "Nenhum Utilizador a Mostrar",
				layout:"fitColumns",
				reactiveData:true, //enable data reactivity
				responsiveLayout: true,
				resizableColumns: false,
				pagination: "local",
				paginationSize: 15,
				paginationSizeSelector: [15, 30, 45, 60, 75, 90, 105], 
				columns: [
					{title: "#", field: "id", width: "7%"},
					{title: "Nome", field: "name"},
					{title: "Email", field: "email"},
					{title: "Tipo", field: "roles.0.display_name"}, 
					{title: "Opções", formatter: this.actions, headerSort: false, width: '10%'}
				],
			});

			const tabulator = this.tabulator;
			$(window).on('resize', function () {
			    tabulator.redraw();
			});
		}, 
		methods: {
			search: function(event){
				var term = this.searchTerm;
				var table = this.tabulator;
				var filters = [];
				var columns = table.getColumns();

				if (term == "") {
					table.clearFilter();
					return
				}

			    columns.forEach(function(column){
			        filters.push({
			            field: column.getField(),
			            type: "like",
			            value: term,
			        });
			    });
				table.setFilter([filters]);
			},
			actions: function(cell, formatterParams, onRendered){ //plain text value
			    return '<a href="/dashboard/users/edit/'+cell.getData().id+'"><i class="fas fa-edit text-info mx-1"></i></a><a href="/dashboard/users/delete/'+cell.getData().id+'"><i class="fas fa-trash-alt text-danger mx-1"></i></a>';
			}
		}
	}
</script>
