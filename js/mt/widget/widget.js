/**
 * @category    MT
 * @package     MT_Widget
 * Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */
;'use strict';

var MTWidgetChooser = Class.create();
MTWidgetChooser.prototype = {
    initialize: function(url){
        this.url = url;
    },
    hideEntityChooser: function(container){
        if ($(container)) {
            $(container).addClassName('no-display').hide();
        }
    },
    displayEntityChooser: function(container){
        var params = {};
        params.url = this.url;
        if (container && params.url){
            container = $(container);
            params.data = {
                id: container.id,
                selected: container.up('td.value').down('input[type="text"].entities').value
            };
            this.displayChooser(params, container);
        }
    },
    displayChooser : function(params, container) {
        container = $(container);
        if (params.url && container) {
            if (container.innerHTML == '') {
                new Ajax.Request(params.url, {
                    method: 'post',
                    parameters: params.data,
                    onSuccess: function(transport) {
                        try {
                            if (transport.responseText) {
                                Element.insert(container, transport.responseText);
                                container.removeClassName('no-display').show();
                            }
                        } catch (e) {
                            alert('Error occurs during loading chooser.');
                        }
                    }
                });
            } else {
                container.removeClassName('no-display').show();
            }
        }
    },
    checkCategory: function(event) {
        var node = event.memo.node;
        var container = event.target.up('td.value');
        var elm = container.down('input[type="text"].entities');
        this.updateEntityValue(node.id, elm, node.attributes.checked);
    },
    checkProduct: function(event){
        var input = event.memo.element,
            container = event.target.up('td.value'),
            elm = container.down('input[type="text"].entities');
        if (!isNaN(input.value)) this.updateEntityValue(input.value, elm, input.checked);
    },
    updateEntityValue: function(value, elm, isAdd){
        var values = $(elm).value.strip();
        if (values) values = values.split(',');
        else values = [];
        if (isAdd){
            if (-1 === values.indexOf(value)){
                values.push(value);
                $(elm).value = values.join(',');
            }
        }else{
            if (-1 != values.indexOf(value)){
                values.splice(values.indexOf(value), 1);
                $(elm).value = values.join(',');
            }
        }
    },
    clearEntityValue: function(container){
        var elm = $(container).up('td.value').down('input[type="text"].entities');
        if (elm) elm.value = '';
        var hidden = $(container).down('input[type="hidden"]');
        if (hidden) hidden.value = '';
        $(container).select('input[type="checkbox"]').each(function(checkbox){
            checkbox.checked = false;
        });
    }
};

var MTLayoutPreview = Class.create();
MTLayoutPreview.prototype = {
    MAX_COLUMN: 12,
    COLUMN_WIDTH: 50,
    TITLES: new Hash(),
    initialize: function(container, fieldset, layout){
        this.container = $(container);
        if (this.container.init) return;
        this.container.init = true;
        this.layout = layout;
        this.fieldset = fieldset;
        this.target = $(fieldset + '_block_ids');
        this.defaultElm = $(fieldset + '_block_' + layout);

        for (var i=0; i<this.target.options.length; i++){
            this.TITLES.set(this.target.options[i].value, this.target.options[i].innerHTML);
        }

        document.observe('layout:order', function(ev){
            this.updateFlexGridOrder(ev.memo.blocks);
        }.bind(this));

        this.COLUMNS = new Hash();
        this.CLASSES = new Hash();

        this.initFlexGrid();
    },
    initFlexGrid: function(){
        this.target.observe('change', function(){
            this.collectFlexGridValues(false);
        }.bind(this));

        this.collectFlexGridValues(true);
    },
    updateFlexGridOrder: function(blocks){
        var items = [];
        blocks.each(function(block){
            var obj = {};
            obj.id = block;
            obj.text = this.TITLES.get(block);
            obj.column = this.COLUMNS.get(block);
            obj.classes = this.CLASSES.get(block);
            items.push(obj);
        }, this);
        this.renderFlexGrid(items);
    },
    collectFlexGridValues: function(isInit){
        var items = [];

        if (isInit){
            var defaultValues = this.defaultElm.value ? this.defaultElm.value.split('|') : [];
            if (defaultValues.length){
                defaultValues.each(function(value){
                    value = value.split(',');
                    items.push({
                        text: this.TITLES.get(value[0]),
                        id: value[0],
                        column: value[1],
                        classes: value[2] ? value[2].strip().split(' ') : []
                    });
                }, this);
            }
        }else{
            var values = this.target.getValue(),
                count = values.length;

            if (!count) return;

            var average_col = Math.floor(this.MAX_COLUMN / count),
                last_col,
                i = 0;

            if (average_col == 0){
                average_col = 1;
                last_col = 0;
            }else{
                last_col = this.MAX_COLUMN - (average_col * count);
            }

            values.each(function(value){
                var obj = {};
                obj.id = value;
                obj.text = this.TITLES.get(value);
                if (i++ < count-1){
                    obj.column = average_col;
                }else{
                    if (last_col == 0) obj.column = average_col;
                    else obj.column = last_col + average_col;
                }
                obj.classes = [];
                items.push(obj);
            }, this);
        }

        this.renderFlexGrid(items);
    },
    renderFlexGrid: function(items){
        var count = items.size();
        if (isNaN(count) || count <= 0) return;

        this.container.update('');
        items.each(function(item){
            this.container.insert(this.renderFlexGridItem(item));
        }, this);

        this.bindFlexGridEvent();
    },
    renderFlexGridItem: function(item){
        var hide = item.classes.indexOf('hidden-' + this.layout) != -1,
            width = item.column * this.COLUMN_WIDTH;

        var divElm = new Element('div', {'class':'flex-grid', 'data-bid':item.id, 'data-column':item.column, 'data-classes':item.classes});
        divElm.setStyle({width: width+'px'});
        divElm.bid = item.id;

        var nameElm = new Element('span', {'class':'flex-grid-name'});
        nameElm.update(item.text);

        var columnElm = new Element('a');
        columnElm.update(item.column);

        var hideElm = new Element('span', {'class':'flex-grid-hide'});
        divElm.addClassName(hide ? 'hidden' : '');
        hideElm.observe('click', function(ev){
            var me = Event.element(ev),
                parent = me.up(),
                store = $(this.fieldset +'_block_'+ this.layout);

            if (parent && store){
                var oldValue = store.value,
                    newValue = [],
                    hideMe = !parent.hasClassName('hidden');

                oldValue.split('|').each(function(str){
                    var parts = str.split(','),
                        cls = 'hidden-' + this.layout;

                    if (parts[0] == parent.bid){
                        if (parts.length === 2){
                            if (hideMe){
                                parts.push(cls);
                                this.CLASSES.set(parent.bid, cls);
                                parent.writeAttribute('data-classes', cls);
                            }
                        }else if (parts.length === 3){
                            var classes = parts[2].split(' '),
                                index = classes.indexOf(cls);

                            if (hideMe){
                                if (index == -1){
                                    classes.push(cls);
                                    this.CLASSES.set(parent.bid, cls);
                                    parent.writeAttribute('data-classes', cls);
                                }
                            }else{
                                if (index != -1){
                                    classes.splice(index, 1);
                                }
                            }
                            parts[2] = classes.join(' ').strip();
                            this.CLASSES.set(parent.bid, parts[2]);
                            parent.writeAttribute('data-classes', parts[2]);
                        }
                        newValue.push(parts.join(','));
                    }else{
                        newValue.push(str);
                    }
                }, this);
                if (newValue.length) store.value = newValue.join('|');
                parent.toggleClassName('hidden');
            }
        }.bind(this));

        divElm.insert(nameElm);
        divElm.insert(columnElm);
        divElm.insert(hideElm);

        return divElm;
    },
    bindFlexGridEvent: function(){
        var self = this,
            layout = this.layout,
            container = jQuery('#layout_'+layout),
            items = container.find('.flex-grid'),
            fieldset = this.fieldset,
            max_column = this.MAX_COLUMN,
            col_width = this.COLUMN_WIDTH;

        function storeFlexGrid(){
            var values = [];
            container.find('.flex-grid').each(function(i, item){
                var item = jQuery(item),
                    tmp = [];

                tmp.push(item.attr('data-bid'));
                tmp.push(item.attr('data-column'));
                tmp.push(item.attr('data-classes'));
                values.push(tmp.join(','));
            });
            jQuery('#' + fieldset + '_block_' + layout).val(values.join('|'));
            self.storeFlexGridValues(values);
        }

        container.sortable({
            appendTo: 'parent',
            containment: 'parent',
            stop: function(){
                storeFlexGrid();
                self.triggerFlexGridOrder();
            }
        });

        items.resizable({
            grid: [col_width],
            handles: 'e',
            maxWidth: max_column * col_width,
            resize: function(ev, ui){
                var width = ui.size.width,
                    column = Math.floor(width/50);

                ui.element.find('a').text(column);
                ui.element.attr('data-column', column);
            },
            stop: function(){
                storeFlexGrid();
            }
        });

        storeFlexGrid();
    },
    triggerFlexGridOrder: function(){
        var blocks = [];
        this.container.select('.flex-grid').each(function(item){
            blocks.push(item.bid);
        });
        Element.fire(this.container, 'layout:order', {blocks: blocks});
    },
    storeFlexGridValues: function(values){
        values.each(function(value){
            value = value.split(',');
            this.COLUMNS.set(value[0], value[1]);
            this.CLASSES.set(value[0], value[2].strip());
        }, this);
    }
};
