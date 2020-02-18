import React from 'react';
import {Button,ButtonGroup} from 'react-bootstrap';

/**
 * Handles navigation from 1 step to another.
 * @param props.step : the current UI step
 * @param props.min: initial step number
 * @param props.max : last step number
 * @param props.setStep: to increment/decrement  the UI step.
 */
export default function (props) {
    const {step,min,max,setStep} = props;

    /* initial step only has a forward buttonm
     * last step has a submit button to commit changes to backend.
     */
    return (
        <ButtonGroup>
        {step > min
         ? <Button variant="primary"onClick= {e => setStep(step-1)}> &lt; </Button>
         : null
        }
        {step < max //BUG : submit button directly pressed when navigating to last step:
        ? <Button variant="primary"onClick= {e => setStep(step+1)}> &gt; </Button>
        : <Button variant="primary" type="submit"> Submit </Button>
        }



        </ButtonGroup>

    );

}
