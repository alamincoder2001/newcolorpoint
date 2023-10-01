<style>
	.v-select {
		margin-top: -2.5px;
		float: right;
		min-width: 180px;
		margin-left: 5px;
	}

	.v-select .dropdown-toggle {
		padding: 0px;
		height: 25px;
	}

	.v-select input[type=search],
	.v-select input[type=search]:focus {
		margin: 0px;
	}

	.v-select .vs__selected-options {
		overflow: hidden;
		flex-wrap: nowrap;
	}

	.v-select .selected-tag {
		margin: 2px 0px;
		white-space: nowrap;
		position: absolute;
		left: 0px;
	}

	.v-select .vs__actions {
		margin-top: -5px;
	}

	.v-select .dropdown-menu {
		width: auto;
		overflow-y: auto;
	}
</style>

<div class="row" id="customerDueList">
	<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
		<form class="form-inline">
			<div class="form-group">
				<select style="padding:0;" class="form-control" v-model="searchType" v-on:change="onChangeSearchType">
					<option value="all">All</option>
					<option value="employee">By Employee</option>
					<option value="customer">By Customer</option>
					<option value="area">By Area</option>
				</select>
			</div>
			<div class="form-group" style="display: none" v-bind:style="{display: searchType == 'employee' ? '' : 'none'}">
				<v-select v-bind:options="employees" v-model="selectedEmployee" label="display_name" placeholder="Select Employee"></v-select>
			</div>
			<div class="form-group" style="display: none" v-bind:style="{display: searchType == 'customer' ? '' : 'none'}">
				<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name" placeholder="Select Customer"></v-select>
			</div>
			<div class="form-group" style="display: none" v-bind:style="{display: searchType == 'area' ? '' : 'none'}">
				<v-select v-bind:options="areas" v-model="selectedArea" label="District_Name" placeholder="Select Area"></v-select>
			</div>
			<div class="form-group">
				<input type="date" class="form-control" v-model="filter.dateFrom" />
			</div>
			<div class="form-group">
				<input type="date" class="form-control" v-model="filter.dateTo" />
			</div>
			<div class="form-group">
				<input type="button" class="btn btn-primary" value="Show Report" v-on:click="getDues" style="padding: 0px;line-height: 1;margin-top: -3px;">
			</div>
		</form>
	</div>

	<div class="col-md-12" style="display: none" v-bind:style="{display: dues.length > 0 ? '' : 'none'}">
		<a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
			<i class="fa fa-print"></i> Print
		</a>
		<div class="table-responsive" id="reportTable">
			<table v-if="searchType != 'employee'" style="display:none;" :style="{display: searchType != 'employee' ? '' : 'none'}" class="table table-bordered">
				<thead>
					<tr>
						<th>Customer Id</th>
						<th>Customer Name</th>
						<th>Owner Name</th>
						<th>Address</th>
						<th>Customer Mobile</th>
						<th>Due Amount</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="data in dues">
						<td>{{ data.Customer_Code }}</td>
						<td>{{ data.Customer_Name }}</td>
						<td>{{ data.owner_name }}</td>
						<td>{{ data.Customer_Address }}</td>
						<td>{{ data.Customer_Mobile }}</td>
						<td style="text-align:right">{{ parseFloat(data.dueAmount).toFixed(2) }}</td>
					</tr>
				</tbody>
				<tfoot>
					<tr style="font-weight:bold;">
						<td colspan="5" style="text-align:right">Total Due</td>
						<td style="text-align:right">{{ dues.reduce((prev, cur) => {return prev + parseFloat(cur.dueAmount)}, 0).toFixed(2) }}</td>
					</tr>
				</tfoot>
			</table>
			<table v-if="searchType == 'employee'" style="display:none;" :style="{display: searchType == 'employee' ? '' : 'none'}" class="table table-bordered">
				<tbody>
					<template v-for="data in dues">
						<tr style="background: gray;color:white;">
							<th colspan="5" style="text-align: center;">{{data.Employee_Name}}</th>
						</tr>
						<tr>
							<th>Customer Name</th>
							<th>Opening Balance</th>
							<th>Sales</th>
							<th>Payment</th>
							<th>Due</th>
						</tr>
						<tr v-for="(item, index) in data.dueCustomer">
							<td>{{ item.Customer_Code }}-{{ item.Customer_Name }}</td>
							<td>{{item.openingBal}}</td>
							<td>{{parseFloat(item.salesTotal).toFixed(2)}}</td>
							<td>{{item.paymentTotal}}</td>
							<td>{{parseFloat((+parseFloat(item.openingBal)+ parseFloat(item.salesTotal)) - parseFloat(item.paymentTotal)).toFixed(2)}}</td>
						</tr>
					</template>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/lodash.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#customerDueList',
		data() {
			return {
				searchType: 'all',
				filter: {
					dateFrom: moment().format('YYYY-MM-DD'),
					dateTo: moment().format('YYYY-MM-DD'),
				},
				customers: [],
				selectedCustomer: null,
				areas: [],
				selectedArea: null,
				employees: [],
				selectedEmployee: null,

				dues: [],
			}
		},
		created() {

		},
		methods: {
			onChangeSearchType() {
				this.customers = [];
				this.areas = [];
				this.employees = [];

				if (this.searchType == 'customer') {
					this.getCustomers();
				} else if (this.searchType == 'area') {
					this.getAreas();
				} else if (this.searchType == 'employee') {
					this.getEmployees();
				}
				if (this.searchType == 'all') {
					this.selectedCustomer = null;
					this.selectedArea = null;
					this.selectedEmployee = null;
				}
			},
			getCustomers() {
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
				})
			},
			getAreas() {
				axios.get('/get_districts').then(res => {
					this.areas = res.data;
				})
			},
			getEmployees() {
				axios.get('/get_employees').then(res => {
					this.employees = res.data;
				})
			},
			getDues() {
				this.filter.employeeId = this.selectedEmployee == null ? null : this.selectedEmployee.Employee_SlNo;
				this.filter.customerId = this.selectedCustomer == null ? null : this.selectedCustomer.Customer_SlNo;
				this.filter.districtId = this.selectedArea == null ? null : this.selectedArea.District_SlNo;

				let url;
				if (this.searchType != 'employee') {
					url = '/get_customer_due';
				} else {
					url = '/get_employeewisedue';
				}

				axios.post(url, this.filter).then(res => {
					let dues = res.data;
					if (this.searchType != 'employee') {
						this.dues = dues.filter(d => parseFloat(d.dueAmount) != 0);
					} else {
						this.dues = dues.filter(due => {
							return due.dueCustomer.length > 0
						});
					}


				})
			},
			async print() {
				let reportContent = `
					<div class="container">
						<h4 style="text-align:center">Customer due report</h4 style="text-align:center">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportTable').innerHTML}
							</div>
						</div>
					</div>
				`;

				var mywindow = window.open('', 'PRINT', `width=${screen.width}, height=${screen.height}`);
				mywindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php'); ?>
				`);

				mywindow.document.body.innerHTML += reportContent;

				mywindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				mywindow.print();
				mywindow.close();
			}
		}
	})
</script>