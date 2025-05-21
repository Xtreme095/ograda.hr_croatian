const { ReactFlowProvider, addEdge, ReactFlow, Controls } = window.ReactFlowRenderer;

class BotFlowBuilder extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            elements: []
        };
    }

    componentDidMount() {
        // Load initial flow data if editing an existing flow
        fetch(`/webhook/load_flow/${this.props.flowId}`)
            .then(res => res.json())
            .then(data => {
                this.setState({ elements: data.flow });
            });
    }

    onConnect = (params) => this.setState((prevState) => ({
        elements: addEdge(params, prevState.elements)
    }));

    saveFlow = () => {
        fetch('/webhook/save_flow', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ flow: this.state.elements, name: 'My Flow' })
        }).then(res => res.json())
          .then(data => alert('Flow saved successfully'));
    }

    render() {
        return (
            <ReactFlowProvider>
                <ReactFlow elements={this.state.elements} onConnect={this.onConnect} style={{ width: '100%', height: '90vh' }}>
                    <Controls />
                </ReactFlow>
                <button onClick={this.saveFlow}>Save Flow</button>
            </ReactFlowProvider>
        );
    }
}

ReactDOM.render(<BotFlowBuilder flowId={FLOW_ID_FROM_SERVER} />, document.getElementById('root'));
