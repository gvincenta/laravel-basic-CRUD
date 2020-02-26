import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import Search from './Search';
import { BrowserRouter, Route, Link, Switch } from 'react-router-dom';
import {
    Accordion,
    Card,
    Button,
    Tooltip,
    OverlayTrigger
} from 'react-bootstrap';
import Add from './Books/Add';
import Export from './Export';
import introJs from 'intro.js';
import 'intro.js/introjs.css';
/**
 * Shows the main page with 3 tabs:
 * 1. Books and Authors table,
 * 2. Adding a book,
 * 3. Export to.
 * @returns a page with 3 vertical tabs (using accordion).
 */
import Alert from './Alert';
function renderTooltip(props) {
    return (
        <Tooltip {...props}>
            For the Guide Me to load up properly, please wait for the Books and
            Authors table to finish loading, and make sure that the Books and
            Authors tab is opened.
        </Tooltip>
    );
}
export default function() {
    return (
        <div>
            <OverlayTrigger
                placement="right"
                delay={{ show: 250, hide: 400 }}
                overlay={renderTooltip}
            >
                <Button
                    onClick={() => introJs().start()}
                    data-step="1"
                    data-intro="Hello there, Welcome to the app!"
                >
                    Guide Me{' '}
                </Button>
            </OverlayTrigger>

            <Accordion defaultActiveKey="0">
                <Card>
                    <Card.Header>
                        <Accordion.Toggle
                            variant="link"
                            eventKey="0"
                            data-step="2"
                            data-intro={
                                'Click here to show all the books and authors available in the database. ' +
                                'If a row has no book, It means that the author of that row is currently not ' +
                                'assigned to any books. ' +
                                'If the same BookID appears more than once, that book has multiple authors. ' +
                                'You can sort any columns in the table by clicking on the column headings.'
                            }
                        >
                            Books And Authors
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="0">
                        <Search />
                    </Accordion.Collapse>
                </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle
                            variant="link"
                            eventKey="1"
                            data-step="7"
                            data-intro={
                                'Click here to add a new book as well as assigning authors to it. ' +
                                'Click Assign to assign authors to the new book. ' +
                                "In the assigned table, click on  the author's name to unassign them from the new book." +
                                'For mononymous names, enter their first name in the "Last Name" field as well. '
                            }
                        >
                            Add a new book
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="1">
                        <Add />
                    </Accordion.Collapse>
                </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle
                            variant="link"
                            eventKey="2"
                            data-step="8"
                            data-intro={
                                'Click here to export books and/or authors data from database to CSV or XML. ' +
                                'Once exported, You will be able to view and download them.'
                            }
                        >
                            Export
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="2">
                        <Export />
                    </Accordion.Collapse>
                </Card>
            </Accordion>
            <p>
                {' '}
                <i> Notice: </i> The book titles and author names filled in this
                app are fictitious. Any similarity to any person living or dead,
                or any book titles, is merely coincidental.{' '}
            </p>
        </div>
    );
}
