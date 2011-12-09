<div id="nav">
    <ul id="sddm">
        <table border="0" align="center">
            <tr>
                <td>
                    <li id="button1"><a href="admin.php">HOME</a></li>
                </td>
                <td>
                    <li id="button2"><a href="#" onmouseover="mopen('m1')" onmouseout="mclosetime()">CONSULTANTS</a>
                        <div id="m1" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <a href="admin_manage_consultants.php?action=add">ADD</a>
                            <a href="admin_manage_consultants.php?action=edit">EDIT</a>
                            <a href="admin_manage_consultants.php?action=delete">DELETE</a>
                            <a href="admin_manage_consultants.php?action=view">VIEW</a>
                            <a href="admin_manage_consultants.php?action=impersonate">IMPERSONATE</a>
                        </div>
                    </li>
                </td>                 
                <td>
                    <li id="button3"><a href="#" onmouseover="mopen('m2')" onmouseout="mclosetime()">SCHEDULE</a>
                        <div id="m2" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <a href="admin_manage_schedule.php?action=add">ADD</a>
                            <a href="admin_manage_schedule.php?action=edit">EDIT</a>
                            <a href="admin_manage_schedule.php?action=view">VIEW</a>
                            <a href="admin_manage_schedule.php?action=delete">DELETE</a>
                            <a href="admin_manage_labs.php">MANAGE LABS</a>
                        </div>
                    </li>
                </td>              
                <td>
                    <li id="button4"><a href="#" onmouseover="mopen('m3')" onmouseout="mclosetime()">UTILITIES</a>
                        <div id="m3" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <a href="admin_utilities.php?action=settings">SETTINGS</a>
                            <a href="admin_utilities.php?action=email">E-MAIL</a>
                            <a href="admin_utilities_log.php">SHIFT LOG</a>
                            <a href="admin_utilities_log_email.php">E-MAIL LOG</a>
                            <a href="admin_utilities_temp_shifts.php">TEMP SHIFTS</a>
                        </div>
                    </li>
                </td>    
                <td>
                    <li id="button5"><a href="logout.php">LOGOUT</a></li>
                </td>                
            </tr>
        </table>
    </ul>
    <div style="clear:both"></div>
</div>