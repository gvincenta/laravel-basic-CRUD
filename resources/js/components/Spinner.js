import React from 'react';
import {Spinner} from 'react-bootstrap';
//displays loading animation:
export default function () {
    return (
        <div className="container" align="center">
            <Spinner animation="border" role="status">
                <span align="center" className="sr-only">Loading...</span>
            </Spinner>
        </div>
        );
}
