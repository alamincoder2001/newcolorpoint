<style>
    .v-select {
        margin-bottom: 5px;
    }

    .v-select.open .dropdown-toggle {
        border-bottom: 1px solid #ccc;
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

    .saveBtn {
        padding: 7px 22px;
        background-color: #00acb5 !important;
        border-radius: 2px !important;
        border: none;
    }

    .saveBtn:hover {
        padding: 7px 22px;
        background-color: #06777c !important;
        border-radius: 2px !important;
        border: none;
    }

    select.form-control {
        padding: 1px;
    }

    .addBtn {
        background: red;
        padding: 3px 8px;
        position: absolute;
        color: #fff;
        border-radius: 2px;
    }
</style>
<div id="vehicle">
    <div class="row" style="margin-top: 10px;margin-bottom:15px;border-bottom: 1px solid #ccc;padding-bottom: 15px;">
        <form class="form-horizontal" v-on:submit.prevent="saveDate">
            <div class="col-md-5 col-md-offset-3">
                <div class="form-group">
                    <label class="control-label col-md-3">Unit</label>
                    <label class="col-md-1" style="text-align: right;">:</label>
                    <div class="col-md-7">
                        <v-select v-bind:options="units" v-model="selectedUnit" label="Unit_Name"></v-select>
                    </div>
                    <div class="col-md-1" style="padding:0;">
                        <a href="/unit" target="_blank" class="add-button addBtn">
                            <i class="fa fa-plus"></i>
                        </a>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3">Size</label>
                    <label class="col-md-1" style="text-align: right;">:</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" v-model="inputField.size_name">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3">Roll/SFT</label>
                    <label class="col-md-1" style="text-align: right;">:</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" v-model="inputField.roll_per_sft">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3">Description</label>
                    <label class="col-md-1" style="text-align: right;">:</label>
                    <div class="col-md-8">
                        <textarea type="text" class="form-control" v-model="inputField.description" cols="30" rows="2"></textarea>
                    </div>
                </div>
                <div class="form-group" style="display: none;" :style="{display: inputField.size_id != '' ? '' : 'none'}">
                    <label class="control-label col-md-3">status</label>
                    <label class="col-md-1" style="text-align: right;">:</label>
                    <div class="col-md-8">
                        <select class="form-control" v-model="inputField.status">
                            <option value="" selected>Select---</option>
                            <option value="a">Active</option>
                            <option value="d">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group clearfix">
                    <div class="col-md-12" style="text-align: right;">
                        <input type="submit" class="btn saveBtn" :disabled="saveProcess" value="Add">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-sm-12 form-inline">
            <div class="form-group">
                <label for="filter" class="sr-only">Filter</label>
                <input type="text" class="form-control" v-model="filter" placeholder="Filter">
            </div>
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <datatable :columns="columns" :data="allSizes" :filter-by="filter">
                    <template scope="{ row }">
                        <tr :style="{color: row.status == 'd' ? 'red' :''}">
                            <td>{{ row.size_id }}</td>
                            <td>{{ row.Unit_Name }}</td>
                            <td>{{ row.size_name }}</td>
                            <td>{{ row.roll_per_sft }}</td>
                            <td>{{ row.description }}</td>
                            <td>{{ row.status == 'a' ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <?php if ($this->session->userdata('accountType') != 'u') {
                                ?>
                                    <a href="" v-on:click.prevent=" editItem(row)"><i class="fa fa-pencil"></i></a>&nbsp;
                                    <a href="" class="button" v-on:click.prevent="deleteItem(row.size_id )"><i class="fa fa-trash"></i></a>
                                <?php  }
                                ?>
                            </td>
                            <td v-else></td>
                        </tr>
                    </template>
                </datatable>
                <datatable-pager v-model="page" type="abbreviated" :per-page="per_page"></datatable-pager>
            </div>
        </div>
    </div>
</div>


<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vuejs-datatable.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>
<script>
    Vue.component('v-select', VueSelect.VueSelect);
    new Vue({
        el: '#vehicle',
        data() {
            return {
                inputField: {
                    size_id: '',
                    size_name: '',
                    roll_per_sft: '',
                    description: '',
                },
                saveProcess: false,
                allSizes: [],
                units: [],
                selectedUnit: null,

                columns: [{
                        label: 'SL',
                        field: 'size_id',
                        align: 'center'
                    },
                    {
                        label: 'Unit Name',
                        field: 'Unit_Name',
                        align: 'center'
                    },
                    {
                        label: 'Size Name',
                        field: 'size_name',
                        align: 'center'
                    },
                    {
                        label: 'Roll Per Sft',
                        field: 'roll_per_sft',
                        align: 'center'
                    },
                    {
                        label: 'description',
                        field: 'description',
                        align: 'center'
                    },
                    {
                        label: 'Status',
                        field: 'status',
                        align: 'center'
                    },
                    {
                        label: 'Action',
                        align: 'center',
                        filterable: false
                    }
                ],
                page: 1,
                per_page: 10,
                filter: ''
            }
        },
        created() {
            this.getSizes();
            this.getUnits();
        },
        methods: {
            getUnits() {
                axios.get('/get_units').then(res => {
                    this.units = res.data;
                })
            },
            getSizes() {
                axios.post('/get-sizes', {
                    status: ''
                }).then(res => {
                    this.allSizes = res.data;
                })
            },
            saveDate() {
                if (this.selectedUnit == null) {
                    alert('Select unit');
                    return;
                }
                if (this.inputField.size_name == '') {
                    alert('Size Name is Required!');
                    return;
                }
                if (this.inputField.roll_per_sft == '') {
                    alert('Roll/SFT required!');
                    return;
                }
                this.inputField.unit_id = this.selectedUnit.Unit_SlNo;
                let url = '/save-size';

                this.saveProcess = true;

                axios.post(url, this.inputField).then(res => {
                    let r = res.data;
                    alert(r.message);
                    if (r.success) {
                        this.saveProcess = false;
                        // this.getClientCode();
                        this.getSizes();
                        this.clearForm();
                    } else {
                        this.saveProcess = false;
                    }
                })
            },
            editItem(data) {
                console.log(data);
                this.inputField.unit_id = data.unit_id;
                this.inputField.size_id = data.size_id;
                this.inputField.size_name = data.size_name;
                this.inputField.roll_per_sft = data.roll_per_sft;
                this.inputField.description = data.description;
                this.inputField.status = data.status;

                this.selectedUnit = {
                    Unit_SlNo: data.unit_id,
                    Unit_Name: data.Unit_Name
                }
            },
            deleteItem(id) {
                let deleteConfirm = confirm('Are Your Sure to delete the item?');
                if (deleteConfirm == false) {
                    return;
                }
                axios.post('/delete-size', {
                    size_id: id
                }).then(res => {
                    let r = res.data;
                    alert(r.message);
                    if (r.success) {
                        this.getSizes();
                    }
                })
            },
            clearForm() {
                this.inputField.size_id = '';
                this.inputField.size_name = '';
                this.inputField.roll_per_sft = '';
                this.inputField.description = '';

                delete this.inputField.status;
                this.selectedUnit = null;
            }

        }
    })
</script>