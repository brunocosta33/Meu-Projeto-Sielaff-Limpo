<template>
	<div class="row">
		<div class="col-10">
			<div class="form-group">
				<input type="text" class="form-control" placeholder="Pesquisa..." @keyup="search($event)" v-model="searchTerm">
			</div>
		</div>
		<div class="col-2 text-right">
			<div class="form-group">
				<a href="/dashboard/roles/create" class="btn btn-md btn-default text-info"><i class="fas fa-plus"></i> NOVO</a>
				<div class="invisible"  @click.prevent="newRole($event)"></div>
			</div>
		</div>
		<div class="col">
			<div ref="rolesTable" class="table table-striped table-responsive-sm"></div>
		</div>
	</div>
</template>

<script>
	export default {
		data: function () {
			return {
				tabulator: null, //variable to hold your table
				tableData: this.roles, //data for table to display
				searchTerm: null
			}
		},
		props: ['roles'],
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
			this.tabulator = new Tabulator(this.$refs.rolesTable, {
				data: this.tableData, //link data to table
				placeholder: "Nenhum Cargo a Mostrar",
				layout:"fitColumns",
				reactiveData:true, //enable data reactivity
				responsiveLayout: true,
				resizableColumns: false,
				pagination: "local",
				paginationSize: 15,
				paginationSizeSelector: [15, 30, 45, 60, 75, 90, 105], 
				columns: [
					{title: "#", field: "id", width: "7%"},
					{title: "Cargo", field: "display_name"}, 
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
			actions: function(cell, formatterParams, onRendered){
				const actionsTpl = new Vue({
			    	template: '<div><a href="/dashboard/roles/edit/'+cell.getData().id+'"><i class="fas fa-edit text-info mx-1"></i></a><a href="/dashboard/roles/delete/'+cell.getData().id+'"><i class="fas fa-trash-alt text-danger mx-1"></i></a></div>',
			    	//@click.prevent="editRole('+cell.getData().id+', $event)"
					methods: this.$options.methods
			    });
			    actionsTpl.$mount();

			    return actionsTpl.$el;
			},
			newRole: function(event){
				axios.get('/dashboard/roles/create').then(function(response){
					var data = response.data;

					$.showModal({
						title: 'Criar Cargo',
						body: $(data).find('#create-form').html(),
						modalClass: "fade", // Additional css for ".modal", "fade" for fade effect
    					modalDialogClass: "modal-dialog-centered",
						onCreate: function (modal) {
							// create event handler for form submit and handle values
							$(modal.element).on("click", "button[type='submit']", function (event) {
								event.preventDefault();
								var $form = $(modal.element).find('form');
								var form = $(modal.element).find('form').serialize();

								$.ajax({
									url: $form.attr('action'),
									type: 'POST',
									data: form,
									beforeSend: function(){
										$(modal.element).find('button[type=submit]').prop('disabled', true);
									},
									success: function(data, textStatus, jqXHR){
										modal.hide();
										window.location.reload();
									},
									error: function(jqXHR, textStatus, errorThrown){
										var errors = jqXHR.responseJSON;

										$.each(errors, function(key, value){
											$('#'+key).addClass('is-invalid').parent().append($('<div/>', {class: 'invalid-feedback'}).text(value[0]));
										});
									}
								});
							})
						}
					});
				}).then()
			},
			editRole: function(role, event){
				axios.get('/dashboard/roles/edit/'+role).then(function(response){
					var data = response.data;

					$.showModal({
						title: 'Editar Cargo',
						body: $(data).find('#edit-form').html(),
						modalClass: "fade", // Additional css for ".modal", "fade" for fade effect
    					modalDialogClass: "modal-dialog-centered",
						onCreate: function (modal) {
							// create event handler for form submit and handle values
							$(modal.element).on("click", "button[type='submit']", function (event) {
								event.preventDefault();
								var $form = $(modal.element).find('form');
								var form = $(modal.element).find('form').serialize();

								$.ajax({
									url: $form.attr('action'),
									type: 'PATCH',
									data: form,
									beforeSend: function(){
										$(modal.element).find('button[type=submit]').prop('disabled', true);
									},
									success: function(data, textStatus, jqXHR){
										modal.hide();
										window.location.reload();
									},
									error: function(jqXHR, textStatus, errorThrown){
										var errors = jqXHR.responseJSON;

										$.each(errors, function(key, value){ 
											debugger
											$('#'+key).addClass('is-invalid').parent().append($('<div/>', {class: 'invalid-feedback'}).text(value[0]));
										});
									}
								});
							})
						}
					});
				}).then()
			}
		}
	}
</script>
