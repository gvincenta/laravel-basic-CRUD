import React from 'react';
import {Col,Form,Row,Button} from 'react-bootstrap';

/**
 *  Inline input fields to handle entering an author's firstName and lastName with a button.
 * @param props.setFirstName to set author's first name.
 * @param props.setLastName to set author's last name.
 * @param props.onClick what to do when button is clicked.
 * @param props.buttonName the display name of the button.
 * @param props.buttonType to specify using the button's onClick trigger or the form's onSubmit trigger.
 * @param props.required whether the firstName and lastName fields should be non-empty when form is submitted.
 * @returns the UI fields.
 */
export default function (props) {
    const {setFirstName,setLastName,onClick,buttonName,buttonType,required} = props;
    return (
       <Row>
            <Col sm="5">
                <Form.Control type="text" placeholder="First Name" onChange={v => setFirstName(v.target.value)}
                required={required} />
            </Col>
            <Col sm="5">
                <Form.Control type="text" placeholder="Last Name" onChange={v => setLastName(v.target.value)}
                required={required} />
            </Col>
            <Col>
            <Button variant="primary"
            onClick={onClick} type={buttonType}>
            {buttonName}
            </Button>
            </Col>
       </Row>
    )

}
