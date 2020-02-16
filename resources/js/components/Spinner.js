import React from 'react';
import {Spinner,ButtonToolbar} from 'react-bootstrap';
//displays loading animation:
export default function () {
    return (
        <div>
            <br/>
            <ButtonToolbar className="mb-3" aria-label="Toolbar with Button groups">
                <div className="container" align="center">
                    <Spinner animation="border" role="status">
                        <span align="center" className="sr-only">Loading...</span>
                    </Spinner>
                </div>
            </ButtonToolbar>

        </div>
        );
}
