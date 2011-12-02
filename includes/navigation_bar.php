<div id="nav">
    <ul id="sddm">
        <table border="0" align="center">
            <tr>
                <td>
                    <li id="button1"><a href="index.php">HOME</a></li>
                </td>
                <td>
                    <li id="button2"><a href="consultant_view_schedule.php?lab_id=1" 
                    					onmouseover="mopen('m1')" onmouseout="mclosetime()">SCHEDULE</a>
                        <div id="m1" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <?php
								$sql = sprintf("SELECT lab_id, display_name FROM labs ORDER BY display_order");
								$result = mysql_query($sql) or die("Unable to fetch Lab List");
								while($row = mysql_fetch_array($result)){
									$link = '<a href="consultant_view_schedule.php?lab_id=';
									$link .= $row['lab_id'];
									$link .= '">';
									$link .= $row['display_name'];
									$link .= '</a>';
									echo $link;
								}
							?>
                        </div>
                    </li>
                </td>                
                <td>
                    <li id="button3"><a href="consultant_view_open_shifts.php?lab_id=0" 
                    					onmouseover="mopen('m2')" onmouseout="mclosetime()">OPEN SHIFTS</a>
                        <div id="m2" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <a href="consultant_view_open_shifts.php?lab_id=0">All</a>
							<?php
								$sql = sprintf("SELECT lab_id, display_name FROM labs ORDER BY display_order");
								$result = mysql_query($sql) or die("Unable to fetch Lab List");
								while($row = mysql_fetch_array($result)){
									$link = '<a href="consultant_view_open_shifts.php?lab_id=';
									$link .= $row['lab_id'];
									$link .= '">';
									$link .= $row['display_name'];
									$link .= '</a>';
									echo $link;
								}
							?>
                        </div>
                    </li>
                </td>    
                <td>
                    <li id="button4"><a href="#" onmouseover="mopen('m3')" onmouseout="mclosetime()">USER OPTIONS</a>
                        <div id="m3" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <a href="consultant_temp_shift_history.php">SHIFT HISTORY</a>
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