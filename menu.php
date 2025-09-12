<!-- Static navbar -->
<div class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li <?php if($page == 'home'){ echo 'class="active"';  } ?>><a href="home.php">Home</a></li>

                <li <?php if($page == 'enlistment'){ echo 'class="active"'; } ?>><a href="registration_search.php">eKonsulta Registration</a></li>

                <li <?php if($page == 'profiling'){ echo 'class="active"'; } ?>><a href="hsa_search.php">Health Screening & Assessment</a></li>

                <li <?php if($page == 'consultation'){ echo 'class="active"'; } ?>><a href="consultation_search.php">Consultation</a></li>

                <li <?php if($page == 'labs'){ echo 'class="active"'; } ?>><a href="labs_search.php">Laboratory/Imaging Result</a></li>

                <li <?php if($page == 'medicine'){ echo 'class="active"'; } ?>><a href="medicine_search.php">Medicine</a></li>
              
                <li <?php if($page == 'reports'){ echo 'class="active"'; } ?> class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports<span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a class="dropdown-item" href="ekas_search.php">Generate eKAS</a></li>
                        <li><a class="dropdown-item" href="epress_search.php">Generate ePresS</a></li>
                        <li><a class="dropdown-item" href="generate_grp_xml.php">Generate eKONSULTA XML Report per Group</a></li>
                        <li><a class="dropdown-item" href="generate_xml_per_individual.php">Generate eKONSULTA XML Report per Individual</a></li>
                    </ul>
                </li>

                <li <?php if($page == 'upload'){ echo 'class="active"'; } ?> class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Uploader<span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a class="dropdown-item" href="upload_local_assignment.php">Registration Masterlist</a></li>
                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->

    </div><!--/.container-fluid -->
</div>
