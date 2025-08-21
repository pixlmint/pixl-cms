This notebook contains outputs of many different types: text, HTML, plots and errors.
# Text outputs

Using `print`, `sys.stdout` and `sys.stderr`

```python
import sys
print('using print')
sys.stdout.write('using sys.stdout.write')
sys.stderr.write('using sys.stderr.write')
```

```python
import logging
logging.debug('Debug')
logging.info('Info')
logging.warning('Warning')
logging.error('Error')
```
# HTML outputs

Using `pandas`. Here we find two representations: both text and HTML.

```python
import pandas as pd
pd.DataFrame([4])
```
```
   0

0  4
```

```python
from IPython.display import display
display(pd.DataFrame([5]))
display(pd.DataFrame([6]))
```
```
   0

0  5
```
```
   0

0  6
```
# Images

```python
%matplotlib inline
```

```python
# First plot
from matplotlib import pyplot as plt
import numpy as np
w, h = 3, 3
data = np.zeros((h, w, 3), dtype=np.uint8)
data[0,:] = [0,255,0]
data[1,:] = [0,0,255]
data[2,:] = [0,255,0]
data[1:3,1:3] = [255, 0, 0]
plt.imshow(data)
plt.axis('off')
plt.show()
# Second plot
data[1:3,1:3] = [255, 255, 0]
plt.imshow(data)
plt.axis('off')
plt.show()
```
![base64 image](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOcAAADnCAYAAADl9EEgAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAADh0RVh0U29mdHdhcmUAbWF0cGxvdGxpYiB2ZXJzaW9uMy4xLjEsIGh0dHA6Ly9tYXRwbG90bGliLm9yZy8QZhcZAAAC40lEQVR4nO3YQQqEQAwAQWfx/1/OfsD1prZL1dFcgtAEZs3MBvR8nl4AOCZOiBInRIkTosQJUfvZcG3LUy5cbLZZR99dTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBC1n07X3LTGO822nl6Bf/AjM5cTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiNpPp7NuWuOl/B4u5HJClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEqDUzT+8AHHA5IUqcECVOiBInRIkTosQJUV9CQA7NqGQ33wAAAABJRU5ErkJggg==)
```
<Figure size 432x288 with 1 Axes>
```
![base64 image](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOcAAADnCAYAAADl9EEgAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAADh0RVh0U29mdHdhcmUAbWF0cGxvdGxpYiB2ZXJzaW9uMy4xLjEsIGh0dHA6Ly9tYXRwbG90bGliLm9yZy8QZhcZAAAC3klEQVR4nO3YMYrEQAwAQevw/7+syw/j7OzepSocJUoawczuHkDPz9sLANfECVHihChxQpQ4Ieq8G84xvnLhn+2xc/XuckKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oSo83Y6+9Aan2l33l6BL+ZyQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHn7XTnoTWAv1xOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcECVOiBInRIkTosQJUeKEKHFClDghSpwQJU6IEidEiROixAlR4oQocUKUOCFKnBAlTogSJ0SJE6LECVHihChxQpQ4IUqcEDW7+/YOwAWXE6LECVHihChxQpQ4IUqcEPULaDUOzB7yGqYAAAAASUVORK5CYII=)
```
<Figure size 432x288 with 1 Axes>
```
# Errors

```python
undefined_variable
```
```
NameError: name 'undefined_variable' is not defined
```
