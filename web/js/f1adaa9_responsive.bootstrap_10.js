/*! Bootstrap integration for DataTables' Responsive
 * ©2015 SpryMedia Ltd - datatables.net/license
 */
(function(e){if(typeof define==='function'&&define.amd){define(['jquery','datatables.net-bs','datatables.net-responsive'],function(a){return e(a,window,document)})}
else if(typeof exports==='object'){module.exports=function(a,d){if(!a){a=window};if(!d||!d.fn.dataTable){d=require('datatables.net-bs')(a,d).$};if(!d.fn.dataTable.Responsive){require('datatables.net-responsive')(a,d)};return e(d,a,a.document)}}
else{e(jQuery,window,document)}}(function(e,d,n,i){'use strict';var t=e.fn.dataTable,a=t.Responsive.display,o=a.modal;a.modal=function(a){return function(d,t,i){if(!e.fn.modal){o(d,t,i)}
else{if(!t){var n=e('<div class="modal fade" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"/></div></div></div>');if(a&&a.header){n.find('div.modal-header').append('<h4 class="modal-title">'+a.header(d)+'</h4>')};n.find('div.modal-body').append(i());n.appendTo('body').modal()}}}};return t.Responsive}));