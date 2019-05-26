const reload_page = (page, order_col, order_type, source) => {
    if(source == 'col' && order_type == 'DESC') {
        order_type = 'ASC';
    } else if(source == 'col' && order_type == 'ASC') {
        order_type = 'DESC';
    }
    $("#search_form").attr("action", "./messagelog?page="+page+"&order_col="+order_col+"&order_type="+order_type);
    $("#search_form").submit();
}