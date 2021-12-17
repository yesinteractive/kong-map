<label>

    <div class="input-group input-group-sm mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text" id="inputGroup-sizing-sm">Display Plugins</span>
        </div>
        <select id='nodeFilterSelect' class="form-control" style="max-width: 200px;">
            <option value=''>Disabled</option>
            <option value='plugin'>Enabled</option>
        </select>    </div>
    <div class="input-group input-group-sm mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text" id="inputGroup-sizing-sm">Search Nodes</span>
        </div>
        <input type="text" id="search" name="search" value="" placeholder="Type Text Then Hit Enter" class="form-control" style="max-width: 200px;">  </div>
<br>
<div id="mynetwork" style="height:70vh; width: 100vw;"></div>
<div class="card" style="    position: fixed;
    top: 90px;
    /* left: 75%; */
    right: 25px;
    width: 25%;">

    <div class="card-body" id="eventSpan" style="background-color: #f1f1f1; ">
        <?php  echo $cluster_info; ?>


    </div>
</div>
<script type="text/javascript">
    const nodeFilterSelector = document.getElementById('nodeFilterSelect')
    var filtersearch = document.getElementById('search')
    //const kongnodeFilterSelector = document.getElementById('kongnodeFilterSelect')
    function startNetwork(data) {
        const container = document.getElementById('mynetwork')
        const options = {}
        var network = new vis.Network(container, data, options)
        network.on("click", function (properties) {
            var nodeID = properties.nodes[0];
            var sNodeLabel = this.body.nodes[nodeID].options.label;
            //var sToolTip = this.body.nodes[nodeID].options.title;
            var sToolTip = this.body.nodes[nodeID].options.details;
            document.getElementById('eventSpan').innerHTML = sToolTip;
        });
    }

    var mynetwork = document.getElementById('mynetwork');
    var x = - mynetwork.clientWidth ;
    var y = - mynetwork.clientHeight / 2;
    var step = 70;


    // create an array with nodes
    var nodes = new vis.DataSet([
        <?php  echo $nodes; ?>
    ]);

    // create an array with edges
    var edges = new vis.DataSet([
        <?php  echo $connections; ?>
        //         { from: 1, to: 4, color:{color:'navy'}},
        //         { from: 4, to: 5 }

    ]);

    /**
     * filter values are updated in the outer scope.
     * in order to apply filters to new values, DataView.refresh() should be called
     */
    let nodeFilterValue = ''
    const edgesFilterValues = {
        friend: true,
        teacher: true,
        parent: true
    }

    /*
      filter function should return true or false
      based on whether item in DataView satisfies a given condition.
    */
    const nodesFilter = (node) => {
        if (nodeFilterValue === '') {
            return node.kongtype !== 'plugin'
        } else if (nodeFilterValue === 'plugin') {return true;}
        //else {return node.label === nodeFilterValue}
        else {return node.details.toLowerCase().includes(nodeFilterValue.toLowerCase()) }
      /*  switch(nodeFilterValue) {
            case('route'):
                return node.kongtype === 'route'
            case('service'):
                return node.kongtype !== 'service'
            case('plugin'):
                return true
            default:
                return true
        }
        */



    }




   const nodesView = new vis.DataView(nodes, { filter: nodesFilter })


    nodeFilterSelector.addEventListener('change', (e) => {
        // set new value to filter variable
        nodeFilterValue = e.target.value
        /*
          refresh DataView,
          so that its filter function is re-calculated with the new variable
        */
        nodesView.refresh()
    })

    filtersearch.addEventListener('change', (e) => {
        // set new value to filter variable
        nodeFilterValue = e.target.value
        /*
          refresh DataView,
          so that its filter function is re-calculated with the new variable
        */
        nodesView.refresh()
    })





    // create a network
    var container = document.getElementById("mynetwork");
    var data = {
        nodes: nodes,
        edges: edges
    };
    //  var options = {};
    var options = {
        interaction:{
            dragNodes:true,
            dragView: true,
            hideEdgesOnDrag: false,
            hideEdgesOnZoom: false,
            hideNodesOnDrag: false,
            hover: false,
            hoverConnectedEdges: true,
            keyboard: {
                enabled: false,
                speed: {x: 10, y: 10, zoom: 0.02},
                bindToWindow: true
            },
            multiselect: false,
            navigationButtons: false,
            selectable: true,
            selectConnectedEdges: true,
            tooltipDelay: 10000,
            zoomView: true,
            nodes: {
                size: 30,
                font: {
                    size: 32
                },
                borderWidth: 2,
                shadow:true
            },
            edges: {
                width: 2,
                shadow:true
            }
        }
    }




    startNetwork({ nodes: nodesView, edges: edges })
    //  var network = new vis.Network(container, data, options);





</script>