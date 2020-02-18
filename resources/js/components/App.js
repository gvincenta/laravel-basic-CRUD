import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import Search from './Search';
import { BrowserRouter, Route, Link, Switch } from 'react-router-dom';
import { Accordion, Card, Button } from 'react-bootstrap';
import Add from './Books/Add';
import Export from './Export';

/**
 * Shows the main page with 3 tabs:
 * 1. Books and Authors table,
 * 2. Adding a book,
 * 3. Export to.
 * @returns a main page with 3 vertical tabs (using accordion).
 */
export default function() {
    const [action, setAction] = useState('');
    const [status, setStatus] = useState('');

    return (
        <div>
            <Accordion defaultActiveKey="0">
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="0">
                            Books And Authors
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="0">
                        <Search />
                    </Accordion.Collapse>
                </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="1">
                            Add a new book
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="1">
                        <Add />
                    </Accordion.Collapse>
                </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="2">
                            Export
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="2">
                        <Export />
                    </Accordion.Collapse>
                </Card>
            </Accordion>
        </div>
    );
}
