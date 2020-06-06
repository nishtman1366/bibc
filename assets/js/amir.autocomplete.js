function aa_autocomplete(boxId,userType,searchStr,callback)
{
    $.getJSON( "ajax_autocomplete.php",{type: userType,search:searchStr}, function( data ) {
        console.log(data);
        if(data.status == 'ok')
        {
          aa_init_list(boxId,data.data,callback);
        }
        
    });
}

function aa_init_list(boxId,data,callback)
{
    list = $('#aa-list');
    if(list.length == 0)
    {
        list = $("<div id=\"aa-list\">test</div>").appendTo("body");
    }
    console.log(list);
    box = $(boxId);
    window.aa_callback_box = box;
    list.css({display:'',top: box.offset().top + box.outerHeight(),left:box.offset().left});
    list.html('');
    if(typeof data == 'object')
        for(i = 0 ; i < data.length; i++)
        {
            name = data[i]['vName'] + ' ' + data[i]['vLastName'];
            $html = '<div class="aa-item" data-id="'+data[i]['id']+'" data-name="'+name+'">';
            $html += name;
            $html += "<small>email: " + data[i]['vEmail'] + "</small>";
            $html += "<small>id: " + data[i]['id'] + "</small>";
            $html += "<small>phone: " + data[i]['vPhone'] + "</small>";
            $html += "</div>";
            
            item = $($html).appendTo(list);
            
            
            if(typeof callback == 'function')
                item.click(function(){
                    callback($(this).data('id'));
                    $('#aa-list').css('display','none');
                    window.aa_callback_box.val($(this).data('name'));
                });                                                        
        }
}

$(document).ready(function(){
    
    $('#searchDriver').keypress(function()
    {
        clearTimeout(window.driverSearchTimeout);
        window.driverSearchTimeout = setTimeout(function(){
            console.log("TIMEOUT");
            searchStr = $('#searchDriver').val();
            aa_autocomplete('#searchDriver','driver',searchStr,function(id){
                $('#iDriverId').val(id);
                console.log(id);
            });
        },1000);    
    }).blur(function(){
        window.driverSearchHiddenTimeout = setTimeout(function(){
            $('#aa-list').css('display','none')
            if($('#searchDriver').val() == '')
                $('#iDriverId').val('');
        },1000)
        
    }).focus(function(){
        clearTimeout(window.driverSearchHiddenTimeout);
    });
    
    
    $('#searchPassenger').keypress(function()
    {
        clearTimeout(window.driverSearchTimeout);
        window.driverSearchTimeout = setTimeout(function(){
            console.log("TIMEOUT");
            searchStr = $('#searchPassenger').val();
            aa_autocomplete('#searchPassenger','rider',searchStr,function(id){
                $('#iUserId').val(id);
                console.log(id);
            });
        },1000);    
    }).blur(function(){
        window.driverSearchHiddenTimeout = setTimeout(function(){
            $('#aa-list').css('display','none')
            if($('#searchPassenger').val() == '')
                $('#iUserId').val('');
        },1000)
        
    }).focus(function(){
        clearTimeout(window.driverSearchHiddenTimeout);
    });
    
});
